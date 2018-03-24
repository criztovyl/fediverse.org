/**
 * All fediverse.org specific classes and functions
 */



/* return a list of all nodes built from the front end html table (to avoid connecting to the database)  */
function get_all_nodes(){
    var a_ret = new Array();
    $(".node").each( function( i, val ) {
        
        var node_id = $(this).find(".node_id").html();
        var node_url = $(this).find(".node_url").attr("href");
        // console.log(node_id + "/" + node_url);

        a_ret[a_ret.length] = {"node_id": node_id, "node_url" : node_url };
        // a_node_ids[a_node_ids.length] = {"node_id": node_id, "node_url" : node_url };
    });
    return a_ret;
}

/* Main function in charge of pining if nodes are up or not  */
function node_up(){
    if (undefined == a_node_ids[0]) {
        // $("#btn-check-up").attr("disabled", false);
        $("#btn-check-up").button('reset').removeClass("check-padding-top");;
        return;
    }

    $("#nodetr-"+a_node_ids[0].node_id).addClass("trprocessing");
    $(".node_lastseen-"+a_node_ids[0].node_id).html( '<span class="label label-default" style="font-size:9px;">PINGING...</span>' );
    
    $.ajax({
        method: "GET",
        crossDomain: true,
        timeout: (1000 * 15),
        url: a_node_ids[0].node_url + "/api/help/test.json"
    })
     .always(function(){
         $("#nodetr-"+a_node_ids[0].node_id).removeClass("trprocessing");
     })
     .done(function( data ) {


         console.log( a_node_ids[0].node_url + "=" + data);
         
         if ('ok' == data){
             $(".up-dot-"+a_node_ids[0].node_id)
                .removeClass("green red")
                .addClass("green");
             $("#nodetr-"+a_node_ids[0].node_id).removeClass("redbg");

             // update last time seen table value (according to client time now)
             var d = new Date();
             d = d.toString();
             $(".node_lastseen-"+a_node_ids[0].node_id).html( d.substr(4, d.length).substr(0, 20) );

             
             // a_node_ids.push(a_node_ids[0]); // add the first element to last
             a_node_ids.shift(); // delete the first element                     
         }else{
             $("#nodetr-"+a_node_ids[0].node_id).addClass("redbg");
             $(".up-dot-"+a_node_ids[0].node_id)
                .removeClass("green red")
                .addClass("red");
             $(".node_lastseen-"+a_node_ids[0].node_id).html( '<span class="label label-default" style="font-size:9px;">ERROR</span>' );
         }

         node_up();
         
     })
     .fail(function( jqXHR, textStatus ) {

         console.log( a_node_ids[0].node_url + "=" + textStatus); 
         $(".up-dot-"+a_node_ids[0].node_id)
            .removeClass("green red")
            .addClass("red");
         $("#nodetr-"+a_node_ids[0].node_id).addClass("redbg");
         $(".node_lastseen-"+a_node_ids[0].node_id).html( '<span class="label label-default" style="font-size:9px;">ERROR</span>' );

         a_node_ids.shift(); // delete the first element
         node_up();
     });             

}



$(function() {
    /* Ping button handler  */
    $("#btn-check-up").click(function(){
        a_node_ids = get_all_nodes();
        $(".lastseenth").html("Ping (local-time)");
        $(this).button("loading").addClass("check-padding-top");
        setTimeout(function(){node_up()}, 1000);
    });

    // add button form submit
    $("#frm-add-node" ).submit(function( event ) {
        
        event.preventDefault();
        $("#btn-add-node-modal").button('loading');
        
        if ($("#node_url").val().replace(" /gi", "") != ""){
            var data_send = $('#frm-add-node').serialize();
            data_send += "&action=add-node";
        }else{
            alert("Please add some url.");
            return;
        }


        var xhr_add_node = $.ajax({
            url: './fedixhr.php',
            type: 'post',
            dataType: 'xml',
            data: data_send,
            success: function(xml) {

	        var result_code = $(xml).find('code').text();
                var result_description = $(xml).find('description').text();
                if (result_code != "ERROR"){
                    $('#add-node-modal').modal('hide');
                    $("#node_url").val("");
                }
                
                alert(result_description);
                
            }
        });

        // fail handling
        xhr_add_node.fail(function( jqXHR, textStatus ) {
            alert("Something wrong happened with the request :(. Contact the admin: " + textStatus);
        });
        // always
        xhr_add_node.always(function(  ) {
            $("#btn-add-node-modal").button('reset');
        });        



        
    });    

    // tabs
    $('#fediverse-tabs a').click(function (e) {
        e.preventDefault()
            $(this).tab('show');
    });
    // map tabs on show: load map
    $('#fediverse-map-tab').on('shown.bs.tab', function (e) {

        if ( ! $( "#map" ).length ) {
            // init map
            $('#fediverse-map-content').append($('<div></div>').attr({ id : "map" })).append("<br>");
            var mymap = L.map('map').setView([-51.77, -59.38], 13);

        
            L.tileLayer('https://api.tiles.mapbox.com/v4/{id}/{z}/{x}/{y}.png?access_token=pk.eyJ1IjoidHV4eHVzIiwiYSI6ImNpa3Iyb2xkYjA0OXV1Z2t0aW5qbWlkemgifQ.Fk9jfppyFhcCKp4T2HVrEQ', {
                maxZoom: 18,
                attribution: 'Map data &copy; <a href="http://openstreetmap.org">OpenStreetMap</a> contributors, ' +
		             '<a href="http://creativecommons.org/licenses/by-sa/2.0/">CC-BY-SA</a>, ' +
		             'Imagery © <a href="http://mapbox.com">Mapbox</a>',
                id: 'mapbox.streets'
            }).addTo(mymap);
            // mymap.invalidateSize();            
        }


        // create all markers
        var a_latlongs = new Array();
        $.each(map_nodes, function( index, node ) {

            if (node.lat && node.lon){


                var pinAnchor = new L.Point(23, 47);
                
                var marker = L.marker([node.lat, node.lon], {
                    'icon' : L.icon({
                        iconUrl: './js/leaflet/images/marker-icon.png',
                        shadowUrl: './js/leaflet/images/marker-shadow.png',
                        iconAnchor: pinAnchor,
                        popupAnchor: L.point(0, 40),
                        class: 'mapmarker'
                    })
                }).addTo(mymap).bindPopup(node.text);
                // add lat longs for later map center
                a_latlongs.push( L.latLng(node.lat, node.lon) );                
            }

        });


        // center on all maps
        var bounds = new L.LatLngBounds(a_latlongs);
        mymap.fitBounds(bounds);
        
        // other map stuffs
        /* L.marker([51.5, -0.09]).addTo(mymap)
           .bindPopup("<b>Hello world!</b><br />I am a popup.").openPopup();

           L.polygon([
           [51.509, -0.08],
           [51.503, -0.06],
           [51.51, -0.047]
           ]).addTo(mymap).bindPopup("I am a polygon."); w

        */
        
        /* 
           var popup = L.popup();
           function onMapClick(e) {
           console.log(e);
           popup.setLatLng(e.latlng).setContent('').openOn(mymap);
           }
           mymap.on('click', onMapClick); */

        
    });
    // datatable
    $('#fediverse-main-table').DataTable({
        "lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "All"] ],
        "pageLength": 50,
        "paging": true,
        "order": [[ 1, "asc" ]]
    });


});
