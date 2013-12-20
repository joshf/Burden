<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

if (!file_exists("../config.php")) {
    header("Location: index.php");
}

require_once("../config.php");

//Check if we can connect
$con = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$con) {
    die("Error: Could not connect to database (" . mysql_error() . "). Check your database settings are correct.");
}

//Check if database exists
$does_db_exist = mysql_select_db(DB_NAME, $con);
if (!$does_db_exist) {
    die("Error: Database does not exist (" . mysql_error() . "). Check your database settings are correct.");
}

//Define Version
$version = "2.0dev";

if ($version == VERSION) {
    die("Information: The latest version of Burden is already installed and an upgrade is not required.");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Burden &middot; Upgrade</title>
<meta name="robots" content="noindex, nofollow">
<link href="../assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<style type="text/css">
body {
    padding-top: 30px;
    padding-bottom: 30px;
}
</style>
<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
<![endif]-->
</head>
<body>
<div class="navbar navbar-default navbar-fixed-top" role="navigation">
<div class="container">
<div class="navbar-header">
<a class="navbar-brand" href="#">Burden</a>
</div>
</div>
</div>
<div class="container">
<div class="page-header">
<h1>Upgrade</h1>
</div>
<?php

$dbhost = DB_HOST;
$dbuser = DB_USER;
$dbpassword = DB_PASSWORD;
$dbname = DB_NAME;

$updatestring = "<?php

//Database Settings
define('DB_HOST', " . var_export($dbhost, true) . ");
define('DB_USER', " . var_export($dbuser, true) . ");
define('DB_PASSWORD', " . var_export($dbpassword, true) . ");
define('DB_NAME', " . var_export($dbname, true) . ");

//Other Settings
define('VERSION', " . var_export($version, true) . ");

?>";

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
<script src="../assets/jquery.min.js"></script>
<script src="../assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>