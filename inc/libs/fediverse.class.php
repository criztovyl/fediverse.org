<?php
/** 
 * Class to handle the fediverse app
 */

class Fediverse{

    // extra data to extend the node instance from values from the database
    var $a_node = Array();

    /* Initialize the node  */
    public function init($a_node){
        // merge the database values for the node, with instance defaults, extra values
        $this->a_node = array_merge($this->a_node, $a_node);
        // sanitize the uri
        $this->a_node["node_uri"] = $this->sanitize_baseurl($a_node["node_uri"]);
        
    }    
    
    // check for the internet connection
    // from here: http://stackoverflow.com/a/4860432
    public function is_internet(){
        $connected = @fsockopen("www.google.com", 80); 
        //website, port  (try 80 or 443)
        if ($connected){
            $is_conn = true; //action when connected
            fclose($connected);
        }else{
            $is_conn = false; //action in connection failure
        }
        return $is_conn;

    }
    
    /* Simple function to sanitize the base url of the node before anything else  */
    function sanitize_baseurl($uri){
        $uri = trim($uri);
        if ("/" == substr($uri, -1) ){
            $uri = substr($uri, 0, -1);
        }
        return $uri;
    }

    
    /* Function to get the GNU social version of the node instance  */
    private function get_node_version(){
        $v_xml = simplexml_load_file($this->a_node['node_uri']."/api/statusnet/version.xml");
        //if (DEBUG) echo "> inspecting node version «".$this->a_node['node_uri']."» : ";
        // if (DEBUG) echo ((string)$v_xml).DEBUG_NEWLINE;
        return ((string)$v_xml);
    }
    /* Function to get the Gnu Social confi xml */
    private function get_node_config(){
        // https://quitter.no/api/statusnet/config.xml
        $v_xml = simplexml_load_file($this->a_node['node_uri']."/api/statusnet/config.xml");
        return Array(
            
            'site_theme' => $v_xml->site->theme,
            'site_email' => $v_xml->site->email,
            'site_timezone' => $v_xml->site->timezone,
            'site_closed' => $v_xml->site->closed,
            'site_inviteonly' => $v_xml->site->inviteonly,
            'site_private' => $v_xml->site->private,
            'site_textlimit' => $v_xml->site->textlimit

        );
        echo $v_xml->site->theme;die();
        // var_dump($v_xml);die();
        
    }
    /* simple function to get the ip from the domain */    
    public function get_node_ip(){
        return gethostbyname(str_replace(Array("https", "http", "://"), "", $this->a_node['node_uri']));
        
    }
    /* this functions uses the maximind geoip data, stored in a local sqlite database
       to get geo data from the node ip address */
    private function get_node_ip_geodata(){


        
    }
    
    /* Check if a node is up. We use cURL to better control de timeout  */
    public function is_up($dodebug = true){
        // https://quitter.no/api/statuses/public_timeline.as
        // https://quitter.se/api/help/test.xml


        $ch = curl_init();

        $curl_headers = Array();
        $curl_headers[] = 'Content-Type: application/json; charset=utf-8';
        //Some servers (like Lighttpd) will not process the curl request without this header and will return error code 417 instead. 
	//Apache does not need it, but it is safe to use it there as well.        
        $curl_headers[] = 'Expect:';
        // $curl_headers[] = 'Content-Length: ' . strlen($xml);

        // $rh = fopen("curl-log.txt", "w"); // open request file handle



        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => $this->a_node['node_uri']."/api/help/test.json",
            CURLOPT_USERAGENT => 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:44.0) Gecko/20100101 Firefox/44.0',
            CURLOPT_POST => 1,
            CURLOPT_POSTFIELDS => array(),
            CURLOPT_VERBOSE => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_MAXREDIRS => 2,
            CURLOPT_HTTPHEADER => $curl_headers,
            CURLINFO_HEADER_OUT => true,
            CURLOPT_SSL_VERIFYHOST => 2/*,
            CURLOPT_STDERR => $rh,
            CURLOPT_HEADER => 1*/
        ));

        if (DEBUG && $dodebug) echo "> checking if node is up «".$this->a_node['node_uri']."» : ";


        
        //execute
        $curl_result = curl_exec($ch);

        // for a future log        
        if ($curl_result === FALSE) {
            $info = curl_getinfo($ch, CURLINFO_HEADER_OUT);


            
            // if (!empty($info)) fwrite($rh, $info); // write the log


            // screen print
            if ($dodebug==true && DEBUG) printf("cUrl error (#%d): %s", curl_errno($ch), htmlspecialchars(curl_error($ch)));
            
            if ($dodebug==true && DEBUG) echo DEBUG_NEWLINE."> Info:";
            if ($dodebug==true && DEBUG) var_dump($info);
        }
        // fclose($rh); // close log
        
         
        
        // parseto json
        $json_result = json_decode($curl_result);


        // update internal instance node data
        $this->a_node['node_up'] = ($json_result=="ok" ? 1 : 0);
        if (DEBUG && $dodebug==true) echo $this->a_node['node_up'].DEBUG_NEWLINE;
        
        return $json_result;
        
    }  


    /* Update all node related data  */
    public function update_node_info($db_geoip, $dodebug = true){

        // if node is down do not query for extra data
        if (0 == $this->a_node['node_up']) return;

        // update instance "last time up" timestamp value
        $this->a_node['node_last_up'] = time();
        if (DEBUG && $dodebug) echo "> timestamp update «".$this->a_node['node_uri']."» : ".$this->a_node['node_last_up'].DEBUG_NEWLINE;        
        
        // query for version
        $this->a_node['node_version'] = $this->get_node_version();
        if (DEBUG && $dodebug) echo "> version update «".$this->a_node['node_uri']."» : ".$this->a_node['node_version'].DEBUG_NEWLINE;

        // query for config
        $a_node_config = $this->get_node_config();
        $this->a_node["node_theme"] = $a_node_config["site_theme"];
        $this->a_node["node_email"] = $a_node_config["site_email"];
        $this->a_node["node_timezone"] = $a_node_config["site_timezone"];
        $this->a_node["node_closed"] = $a_node_config["site_closed"];
        $this->a_node["node_inviteonly"] = $a_node_config["site_inviteonly"];
        $this->a_node["node_private"] = $a_node_config["site_private"];
        $this->a_node["node_textlimit"] = $a_node_config["site_textlimit"];

        if (DEBUG && $dodebug) echo "> config update «".$this->a_node['node_uri']."» : ".(!empty($a_node_config["site_theme"])? "ok" : "error").DEBUG_NEWLINE;

        // get node ip
        $this->a_node['node_ip'] = $this->get_node_ip();
        if (DEBUG && $dodebug) echo "> node «".$this->a_node['node_uri']."» ip : ".$this->a_node["node_ip"].DEBUG_NEWLINE;

        
        // get geodata
        // criztovyl: Missing GeoData DB, so remove this for now.
        //$node_geodata = $db_geoip->get_geoipdata($this->a_node["node_ip"]);
        //if (!empty($node_geodata)){

        //    $this->a_node["node_country"] = $node_geodata["country"];
        //    $this->a_node["node_city"] = $node_geodata["city"];
        //    $this->a_node["node_latitude"] = $node_geodata["latitude"];
        //    $this->a_node["node_longitude"] = $node_geodata["longitude"];

        //    if (DEBUG && $dodebug) echo "> geo data OK. Country: ".$this->a_node["node_country"];
        //    if (DEBUG && $dodebug) echo ", City: ".$this->a_node["node_city"];
        //    if (DEBUG && $dodebug) echo ", Lat/Long: ".$this->a_node["node_latitude"]."/".$this->a_node["node_longitude"];
        //    if (DEBUG && $dodebug) echo DEBUG_NEWLINE;

        //}else{
        //    if (DEBUG && $dodebug) echo "> could NOT get geo data because of wrong ip OR ip is not in MaxMid database : ".$this->a_node["node_ip"].DEBUG_NEWLINE;
        //}
        
        
        
    }


    /* Function to generate the html inc file to display in fediverse-map homepage */
    public function generate_html($nodes, $dodebug = true){

        // criztovyl: GeoData does not work currently, so do not pollute the output.
        //$map_data = Array();

        $html_output = "<div class='table-responsive'>";
        $html_output .= "<table id='fediverse-main-table' class='table table-striped display'>";
        $html_output .= "<thead>";
        $html_output .= "  <tr>";
        $html_output .= "     <th>#</th>";
        $html_output .= "     <th>Federeted Node</th>";
        $html_output .= "     <th>Version</th>";
        $html_output .= "     <th>Invite-Only</th>";
        $html_output .= "     <th>Text-Limit</th>";
        //$html_output .= "     <th>Country</th>";
        
        // $html_output .= "     <th>Timezone</th>";        
        // $html_output .= "     <th>Theme</th>";
        // $html_output .= "     <th>Closed</th>";
        // $html_output .= "     <th>Private</th>";
        $html_output .= "     <th>Details</th>";        
        $html_output .= "     <th class='lastseenth'>Ping (host-time)</th>";
        $html_output .= "  </tr>";
        $html_output .= "<thead><tbody>";        
        foreach ($nodes as $node){
            // country
            $country_info = $node["node_country"].(!empty($node["node_city"]) ? ", ".$node["node_city"] : "");
            if (empty($country_info)) $country_info = $node["node_ip"];

            // invite only
            $inviteonly_info = ($node["node_inviteonly"]=="false" ? "No" : "Yes");

            // node text limit
            $text_limit = ($node["node_textlimit"] != "false" ? $node["node_textlimit"] : "no&nbsp;limit");

            // is nsfw
            $nsfw_label = ($node["is_nsfw"]==1 ? "&nbsp;<span class='label label-danger'>NSFW</span>" : "" );
            
            
            
            $html_output .= "<tr class='node".($node["node_up"] ? "" : " redbg")."' id='nodetr-".$node["node_id"]."'>";
            $html_output .= "  <td class='node_id'>".$node["node_id"]."</td>";
            $html_output .= "  <td><a href='".$node["node_uri"]."' target='_blank' class='node_url'>".str_replace(Array("https", "http", "://"), "", $node["node_uri"])."</a>".$nsfw_label."<span class='up-dot up-dot-".$node["node_id"]." ".($node["node_up"] ? 'green': 'red')."'></span></td>";
            $html_output .= "  <td>".$node["node_version"]."</td>";
            $html_output .= "  <td>".$inviteonly_info."</td>";
            $html_output .= "  <td>".$text_limit."</td>";
            //$html_output .= "  <td>".$country_info."</td>";
            
            // $html_output .= "  <td>".$node["node_timezone"]."</td>";            
            // $html_output .= "  <td>".$node["node_theme"]."</td>";
            // $html_output .= "  <td>".$node["node_closed"]."</td>";
            // $html_output .= "  <td>".$node["node_private"]."</td>";
            
            $html_output .= "  <td><a href='#soon'>Details</a></td>";                                           
            $html_output .= "  <td style='font-size:10px;' class='node_lastseen-".$node["node_id"]."'>".date("M d Y H:i:s", $node["node_last_up"])."</td>";
            $html_output .= "</tr>";

            // add node map data
            //$map_data[] = Array(
            // criztovyl: GeoData does not work currently, so do not pollute the output.
            //    'text' => '<a target="_blank" href="'.$node["node_uri"].'">'.str_replace(Array("http", "https", "://"), "", $node["node_uri"]).'</a><br />'.$node["node_country"],
            //    'lat' => $node["node_latitude"],
            //    'lon' => $node["node_longitude"]
            //);
        }

        $html_output .= "</tbody></table>";
        $html_output .= "</div>";

        // add map to the output, before file creation
        // criztovyl: GeoData does not work currently, so do not pollute the output.
        //$html_output .= "\n<script>var map_nodes = ".json_encode($map_data).";</script>";

        @unlink(FEDIVERSE_NODES_TABLE);
        $fh = fopen(FEDIVERSE_NODES_TABLE, "w");
        $ret = fwrite($fh, $html_output);
        fclose($fh);



        if (DEBUG && $dodebug) echo "> generating file: ".(string)$ret.DEBUG_NEWLINE;
        
        return $ret;

    }

}

?>
