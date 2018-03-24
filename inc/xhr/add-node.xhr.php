<?php
// add node backend

$node_url = sanitize_url($_REQUEST["node_url"]);


// check if url is valid
if (!filter_var($node_url, FILTER_VALIDATE_URL) === false) {

    // init
    $fedib = new Fedib(FEDIVERSE_DB); // fediverse sqlite database handler
    $fediverse = new Fediverse(); // instance to handle communication with the dumb node (previous to database add)
    $fedb_geoip = new Fedib(GEOIP_DB); // geoip database

    
    // dumb node init
    $fediverse->init(Array(
        'node_uri' => $node_url,
        'node_up' => 0
    ));

    
    if ( "ok" == $fediverse->is_up(false) ){

        // try to add node
        $add_result = $fedib->add_node($node_url);

        if (TRUE == $add_result["result"]){
            /* 
               $fh = fopen("./nodes-to-add.txt", "a");
               fwrite($fh, $node_url."\r\n");
               fclose();
             */
            
            // get the node that was just added

            $a_nodes = $fedib->get_nodes($add_result["node_id"]);

            if (isset($a_nodes[0])){



                // now lets parse the node
                $new_fedinode = new Fediverse();
                $new_fedinode->init($a_nodes[0]);
                $new_fedinode->a_node['node_up'] = 1; // just set the internal value as we already know the node is up
                $new_fedinode->update_node_info( $fedb_geoip, false );
                // save in the database new node values
                $fedib->update_node($new_fedinode->a_node);                    


                
                // after all nodes are processed lets generate the html table to include in frontend
                $fediverse->generate_html($fedib->get_nodes(), false);


                // close dbs
                $fedib->close();
                $fedb_geoip->close();                    
                
                
                $xml_return_code = "OK";
                $xml_return_description = "Thanks for sharing. The node was added, and will be processed automatically in around 5-15 minutes :>)";
                

            }else{

                $xml_return_code = "ERROR";
                $xml_return_description = "The node was added but something went wrong when trying to fetch it back again from the database.";                    

            }
            

            
        }else{


            $xml_return_code = "OK";
            if ("NODE_ALREADY_EXISTS" == $add_result["code"]){
                $xml_return_description = "That node was already added before. Seems that good news fly :>)";
            }else{
                $xml_return_description = "Something went wrong when trying to add the node. Sorry :>(";
            }
            
            
        }

    }else{
        // node is down message
        $xml_return_code = "ERROR";
        $xml_return_description = "The node seems DOWN for our server. Please try back when your node is up. Thanks!";            


    }

    
} else {
    
    
    $xml_return_code = "ERROR";
    $xml_return_description = "The url provided does not seems to be valid. Please check.";

    
}    
 

 



?>
