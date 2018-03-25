<?php
// load basic stuff
require_once("inc/load-libs.inc.php");
require_once("inc/fediverse.config.inc.php");

$fedib = new Fedib(FEDIVERSE_DB); // fediverse sqlite database handler
$fediverse = new Fediverse(); // instance to handle communication with nodes

$header_inc = "./data/site-content/".$site_section.".headers.php";
if (file_exists($header_inc)) require_once($header_inc);

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        
        <title><?php echo $a_page["header"]["title"]; ?></title>
        <?php if (!empty($a_page["header"]["description"])) echo '<meta name="description" content="'.$a_page["header"]["description"].'">';  ?>
        
        <!-- Fediverse-map custom styles -->
        <link rel="stylesheet" href="/css/fediverse-map.css">
        <!-- Leaflet -->
        <link rel="stylesheet" href="/js/leaflet/leaflet.css">
        <!-- simple favicon  -->
        <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
        <link rel="icon" href="/favicon.ico" type="image/x-icon">        
        <style> /* padding for navbar-fixed-top */ body { padding-top: 70px; } </style>
    </head>
    
    <body>

        <!-- NAV  -->
        <nav class="navbar navbar-inverse navbar-fixed-top">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="/">
                        <img src="/images/fediverse-logo.png" class="logo-img" style="" />
                        &nbsp;Fediverse
                    </a>
                </div>
                <div id="navbar" class="navbar-collapse collapse">

                    <ul class="nav navbar-nav navbar-left">
                        <li <?php echo menu_sel("home", $site_section, true);  ?>><a href="/">Home</a></li>
                        <?php /*
                        <li <?php echo menu_sel("about", $site_section, true);  ?>><a href="/about">About</a></li>                                                
                        <li <?php echo menu_sel("news", $site_section, true);  ?>><a href="/news">News</a></li>
                        <li <?php echo menu_sel("to-do", $site_section, true);  ?>><a href="/to-do">To Do</a></li>
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">How-Tos <span class="caret"></span></a>
                            <ul class="dropdown-menu">
                                <li><a href="#">How-to-1 (soon)</a></li>
                                <li><a href="#">How-to-2 (soon)</a></li>
                                <li><a href="#">How-to-3 (soon)</a></li>
                                <li role="separator" class="divider"></li>
                                <li><a href="#">General F.A.Q</a></li>
                            </ul>
                        </li>

                        <li><a target="_blank" href="https://quitter.no/tuxxus">Contact</a></li>                        
                         */ ?>
                    </ul>
                    
                    <ul class="nav navbar-nav navbar-right">
                        <?php /*
                        <li class="btn-flattr">
                            <!-- flattr  -->
                            <script id='fb8zjdj'>(function(i){var f,s=document.getElementById(i);f=document.createElement('iframe');f.src='//button.flattr.com/view/?fid=vonky3&button=compact&url='+encodeURIComponent(document.URL);f.title='Flattr';f.height=20;f.width=110;f.style.borderWidth=0;s.parentNode.insertBefore(f,s);})('fb8zjdj');</script>
                        </li>
                         */ ?>
                        <li>
                            <button class="btn btn-success" data-toggle="modal" data-target="#add-node-modal" style="margin-top:8px;margin-left:10px;">Add node</button>
                        </li>
                    </ul>
                </div><!--/.nav-collapse -->

            </div>
        </nav>

        <?php
        $section_inc = "./data/site-content/".$site_section.".html.php";
        if (file_exists($section_inc)){
            require_once($section_inc);
        }else{
            echo "<script>location='http://fediverse.org/404.html';</script>";
        } 
        ?>

        <!-- Add Node Modal  -->
        <div class="modal fade" id="add-node-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">Add node</h4>
                    </div>
                    <form id="frm-add-node" action="" method="POST">
                        <div class="modal-body">

                            <p>
                                If you are and admin and want to add your node to the list you are very welcome!
                                <br />
                                <strong>Make sure your node is up and running. After submitting it may take a while until its added, so be patient.</strong>
                            </p>
                            <p>
                                This is now an automatic process.
                            </p>
                            <p>
                                <label for="node_url">Node Url</label>
                                <input type="url" id="node_url" name="node_url" class="form-control" placeholder="node url (eg. http://quitter.no)...">                            
                            </p>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button id="btn-add-node-modal" data-loading-text="Adding..."  type="submit" class="btn btn-primary">Add node</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>        

        <!-- Javascript  -->
        <script src="/js/fediverse-libs.js"></script>
        <script src="/js/fediverse.js"></script>
        
    </body>
</html>
