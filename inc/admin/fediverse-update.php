<?php
/*
 * Parser for all configured GNU Social nodes
 * In general works this way:
 *     Nodes are configured in a sqlite db, located at the root
 *     The parser queries the nodes configured in the db, when needed
 *     Depending on the timestamp saved on the last parse, stored in the sqlite db
 *     After updating database values, this parser generates a html file with a table representing the latests values from the database
 *     This html table will be included in the frontend. So visitors only load a simple html file
 * Configuration values can be found at /inc/fediverse.config.inc.php
 */


// start here: https://quitter.se/rsd.xml
// server configuration: https://quitter.no/api/statusnet/config.xml
// server version: https://quitter.se/api/statusnet/version.xml
// ??? : https://quitter.se/api/statusnet/app/service.xml

// init
$fedib = new Fedib(FEDIVERSE_DB); // fediverse sqlite database handler
$fedb_geoip = new Fedib(GEOIP_DB); // geoip database
$fediverse = new Fediverse(); // instance to handle communication with nodes
$update_node_info = isset($update_node_info) ? $update_node_info : false;

if (FALSE == $fediverse->is_internet()) die("Server has no connection to Inet. Craps.".DEBUG_NEWLINE);


if (DEBUG) echo DEBUG_NEWLINE."> server connection ok. proceed.".DEBUG_NEWLINE.DEBUG_NEWLINE;

// define nodes to check
if (isset($_GET["node"]) && !empty($_GET["node"])){
    $q_nodes = $fedib->get_nodes($_GET["node"]);
}else{
    $q_nodes = $fedib->get_nodes();
}


foreach ($q_nodes as $node){

    // init instance of the node with db values and more
    $fediverse->init($node);

    // check if node is down/up
    $fediverse->is_up();

    if($update_node_info) {
        // query and update new node values, pass the geoip database instance to query there for the geoup
        $fediverse->update_node_info( $fedb_geoip );
    }

    // save in the database new node values
    $fedib->update_node($fediverse->a_node);

    if (DEBUG) echo DEBUG_NEWLINE;

}


// after all nodes are processed lets generate the html table to include in frontend
$fediverse->generate_html($fedib->get_nodes());


// db close
$fedib->close();
$fedb_geoip->close();
