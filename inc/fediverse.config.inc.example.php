<?php
/**
 * Fediverse-Map configuration vars
 */

if(!defined("DEBUG"))
    define("DEBUG", true); // enable/disable debug outputs

define("DEBUG_NEWLINE", "\n");
// define("DEBUG_NEWLINE", "\r\n");

define("FEDIVERSE_GITHUB", "https://github.com/tuxxus/fediverse.org");
define("FEDIVERSE_DB", "./data/dbs/fedib.db"); // sqlite database file for the frediverse
// sqlite database file for geoip. Data provided by http://www.maxmind.com
// Creative Commons Attribution-ShareAlike 3.0 Unported License
define("GEOIP_DB", "./data/dbs/geoip.db");

define("FEDIVERSE_NODES_TABLE", "./data/node-table.html"); // path where to parser should generate the html table of nodes info

// Stuff related to the admin cli interface
// token that must be sent via param as an extra security
define("FEDIVERSE_ADMIN_TOKEN", ""); // <-- Place a token here.
// allowed actions for the admin interface
$admin_allowed_actions = Array(
    'generate-table',
    'fediverse-parse',
    'fediverse-up'
);

// xhr allowed actions
$xhr_allowed_actions = Array(
    'node-up',
    'add-node'
);

// site layout handling (frontend)
define("SITE_DEFAULT_TITLE", "Fediverse.org");
$site_section = (isset($_REQUEST["section"]) ? $_REQUEST["section"] : "home");


?>
