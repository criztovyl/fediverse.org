#!/opt/lampp/bin/php
<?php
/**
 * Admin interface (for the command line)
 */

// fadmin-specific configuration
set_time_limit(0);
ini_set('max_execution_time', (60 * 15));

// load basic config files
require_once("inc/fediverse.config.inc.php");

// check that this is only run in the command line

// if (!isset($argv)) die("> Woosp! This does not seems like a CLI. Does not run in browser.".DEBUG_NEWLINE);

// To use $_GET so you dont need to support both if it could be used from command line and from web browser.
// by hamboy75, url=http://php.net/manual/es/reserved.variables.argv.php
foreach ($argv as $arg) {
    $e=explode("=",$arg);
    if(count($e)==2)
        $_GET[$e[0]]=$e[1];
    else   
        $_GET[$e[0]]=0;
}



// actual process
if (isset($_GET["token"])
    && $_GET["token"] == FEDIVERSE_ADMIN_TOKEN
    && isset($_GET["action"])
    && in_array($_GET["action"], $admin_allowed_actions)
){
    
    // include fundamental stuff
    require_once("inc/load-libs.inc.php");

    // include admin action
    $inc_admin_file = "inc/admin/".$_GET["action"].".action.php";
    if (file_exists($inc_admin_file)){
        require_once($inc_admin_file);
    }
    
}else{
    die("> Woops, just *wrong* something happened".DEBUG_NEWLINE);
}


?>
