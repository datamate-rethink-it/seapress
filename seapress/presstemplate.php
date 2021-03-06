<?php

if ( ! defined( 'ABSPATH' ) ) exit;

$paths = plugin_dir_url(__FILE__);
$max_upload = (int)(ini_get('upload_max_filesize'));
$max_post = (int)(ini_get('post_max_size'));
$memory_limit = (int)(ini_get('memory_limit'));
$upload_mb = min($max_upload, $max_post, $memory_limit);

$header_template = <<<HEADER
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Seapress</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="$paths/img/favicon.png" />
    <!--[if IE]>
    <link rel="shortcut icon" href="$paths/img/favicon.png"/>
    <![endif]-->




HEADER;

$main_template = <<<MAIN
    <!-- Footable modified styles -->
    <link href="$paths/css/footable.mod.core.css" rel="stylesheet" type="text/css" />
    <link href="$paths/css/footable.mod.standalone.css" rel="stylesheet" type="text/css" />
    <link href="$paths/css/styles.css" rel="stylesheet" type="text/css" />

    <!-- 19px to make the container go all the way to the bottom of the topbar -->
    <style>
    .navbar-static-top {
    margin-bottom: 19px;
    }
    </style>

    <link href="$paths/css/bootstrap.min.css" rel="stylesheet">
    <link href="$paths/css/bootstrap-theme.min.css" rel="stylesheet">
    <script type="text/javascript">
        var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
        </script>
      <script src="$paths/js/html5shiv.min.js"></script>


    </head>
<body>
    <div class="container">
    ##OTHER_CONTENT##
    ##TABLE_RESULTS##
    </div> <!-- /container -->

    <script src="$paths/js/jquery.min.js"></script>
    <script src="$paths/js/bootstrap.min.js"></script>
  <!--  <script src="$paths/js/footable.all.min.js" type="text/javascript"></script>-->
        <script src="$paths/js/upload.js"></script>

MAIN;


$login_template = <<<LOGIN

    <link href="$paths/css/bootstrap.min.css" rel="stylesheet">
    <link href="$paths/css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="$paths/css/login.css" rel="stylesheet">

    <script src="$paths/js/html5shiv.min.js"></script>


      </head>
<body>
<div class="container">
    ##ERROR_ALERT##
    <form method="POST" class="form-signin" role="form">
           <h2 class="form-signin-heading">Login</h2>

           <div class="input-group input-group-sm">
               <span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
           <input type="username" class="form-control" name="username" id="username" placeholder="E-Mail" required autofocus>
           </div>
           <div class="input-group input-group-sm">
               <span class="input-group-addon"><i class="glyphicon glyphicon-lock"></i></span>
           <input type="password" class="form-control"  name="password" id="password" placeholder="Passwort" required>
           </div>

           <div class="input-group input-group-sm">
               <span class="input-group-addon"><i class="glyphicon glyphicon-globe"></i></span>
           <input type="hostname" class="form-control"  name="hostname" id="hostname" placeholder="Cloud Url" required>
           </div>

           <p></p>
           <input name="login" type="hidden" value="Einloggen">
           <button class="btn btn-lg btn-primary btn-block" type="submit">Login</button>
         </form>

</div> <!-- /container -->


    <script src="$paths/js/jquery.min.js"></script>
    <script src="$paths/js/bootstrap.min.js"></script>

LOGIN;


$footer_template = <<<FOOTER
  </body>
</html>
FOOTER;

?>
