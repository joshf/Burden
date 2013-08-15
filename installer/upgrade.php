<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

if (!file_exists("../config.php")) {
    header("Location: index.php");
}

require_once("../config.php");

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Burden &middot; Upgrade</title>
<meta name="robots" content="noindex, nofollow">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="../resources/bootstrap/css/bootstrap.min.css" type="text/css" rel="stylesheet">
<link href="../resources/bootstrap/css/bootstrap-responsive.min.css" type="text/css" rel="stylesheet">
<style type="text/css">
body {
    padding-top: 60px;
}
@media (max-width: 980px) {
    body {
        padding-top: 0;
    }
}
</style>
<!-- Javascript start -->
<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="../resources/jquery.min.js"></script>
<script src="../resources/bootstrap/js/bootstrap.min.js"></script>
<!-- Javascript end -->
</head>
<body>
<!-- Nav start -->
<div class="navbar navbar-fixed-top">
<div class="navbar-inner">
<div class="container">
<a class="brand" href="#">Burden</a>
</div>
</div>
</div>
<!-- Nav end -->
<!-- Content start -->
<div class="container">
<div class="page-header">
<h1>Upgrade</h1>
</div>
<?php

//Version
$version = "1.6";

if ($version == VERSION) {
    die("<div class=\"alert alert-info\"><h4 class=\"alert-heading\">Upgrade Notice</h4><p>Burden does not require an upgrade.<p><a href=\"../login.php\" class=\"btn btn-info\">Go To Login</a></p></div></div></body></html>");
    
}

$dbhost = DB_HOST;
$dbuser = DB_USER;
$dbpassword = DB_PASSWORD;
$dbname = DB_NAME;
$adminuser = ADMIN_USER;
$adminpassword = ADMIN_PASSWORD;
$salt = SALT;
$theme = THEME;

$updatestring = "<?php

//Database Settings
define('DB_HOST', " . var_export($dbhost, true) . ");
define('DB_USER', " . var_export($dbuser, true) . ");
define('DB_PASSWORD', " . var_export($dbpassword, true) . ");
define('DB_NAME', " . var_export($dbname, true) . ");

//Admin Details
define('ADMIN_USER', " . var_export($adminuser, true) . ");
define('ADMIN_PASSWORD', " . var_export($adminpassword, true) . ");
define('SALT', " . var_export($salt, true) . ");

//Other Settings
define('THEME', 'default');
define('VERSION', " . var_export($version, true) . ");

?>";

//Check if we can connect
$con = mysql_connect($dbhost, $dbuser, $dbpassword);
if (!$con) {
    die("<div class=\"alert alert-error\"><h4 class=\"alert-heading\">Update Failed</h4><p>Error: Could not connect to database (" . mysql_error() . "). Check your database settings are correct.</p><p><a class=\"btn btn-danger\" href=\"javascript:history.go(-1)\">Go Back</a></p></div></div></body></html>");
}

//Check if database exists
$does_db_exist = mysql_select_db($dbname, $con);
if (!$does_db_exist) {
    die("<div class=\"alert alert-error\"><h4 class=\"alert-heading\">Update Failed</h4><p>Error: Database does not exist (" . mysql_error() . "). Check your database settings are correct.</p><p><a class=\"btn btn-danger\" href=\"javascript:history.go(-1)\">Go Back</a></p></div></div></body></html>");
}

//Alter Data table
//From 1.5 --> 1.6
$altertable = "ALTER TABLE `Data` CHANGE `id` `id` SMALLINT(10) NOT NULL AUTO_INCREMENT";
 
//Run query
mysql_query($altertable);

//Write Config
$configfile = fopen("../config.php", "w");
fwrite($configfile, $updatestring);
fclose($configfile);

mysql_close($con);

?>
<div class="alert alert-success">
<h4 class="alert-heading">Upgrade Complete</h4>
<p>Burden has been successfully upgraded to version <?php echo $version; ?>.<p><a href="../login.php" class="btn btn-success">Go To Login</a></p>
</div>
</div>
<!-- Content end -->
</body>
</html>