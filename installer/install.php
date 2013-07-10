<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

if (!isset($_POST["doinstall"])) {
    header("Location: index.php");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Burden &middot; Installer</title>
<meta name="robots" content="noindex, nofollow">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="../resources/bootstrap/css/bootstrap.min.css" type="text/css" rel="stylesheet">
<link href="../resources/bootstrap/css/bootstrap-responsive.min.css" type="text/css" rel="stylesheet">
<style type="text/css">
body {
    padding-top: 60px;
}
</style>
<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
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
<h1>Installer</h1>
</div>		
<?php

//Get new settings from POST
$dbhost = $_POST["dbhost"];
$dbuser = $_POST["dbuser"];
$dbpassword = $_POST["dbpassword"];
$dbname = $_POST["dbname"];
$adminuser = $_POST["adminuser"];
if (empty($_POST["adminpassword"])) {
    die("<div class=\"alert alert-error\"><h4 class=\"alert-heading\">Install Failed</h4><p>Error: No admin password set.</p><p><a class=\"btn btn-danger\" href=\"javascript:history.go(-1)\">Go Back</a></p></div></div></body></html>");
} else {
    $adminpassword = sha1($_POST["adminpassword"]);
}
$uniquekey = md5(microtime().rand());

$installstring = "<?php

//Database Settings
define('DB_HOST', " . var_export($dbhost, true) . ");
define('DB_USER', " . var_export($dbuser, true) . ");
define('DB_PASSWORD', " . var_export($dbpassword, true) . ");
define('DB_NAME', " . var_export($dbname, true) . ");

//Admin Details
define('ADMIN_USER', " . var_export($adminuser, true) . ");
define('ADMIN_PASSWORD', " . var_export($adminpassword, true) . ");

//Other Settings
define('UNIQUE_KEY', " . var_export($uniquekey, true) . ");
define('THEME', 'default');

?>";

//Check if we can connect
$con = mysql_connect($dbhost, $dbuser, $dbpassword);
if (!$con) {
    die("<div class=\"alert alert-error\"><h4 class=\"alert-heading\">Install Failed</h4><p>Error: Could not connect to database (" . mysql_error() . "). Check your database settings are correct.</p><p><a class=\"btn btn-danger\" href=\"javascript:history.go(-1)\">Go Back</a></p></div></div></body></html>");
}

//Check if database exists
$does_db_exist = mysql_select_db($dbname, $con);
if (!$does_db_exist) {
    die("<div class=\"alert alert-error\"><h4 class=\"alert-heading\">Install Failed</h4><p>Error: Database does not exist (" . mysql_error() . "). Check your database settings are correct.</p><p><a class=\"btn btn-danger\" href=\"javascript:history.go(-1)\">Go Back</a></p></div></div></body></html>");
}

//Create Data table
$createtable = "CREATE TABLE Data (
id SMALLINT(10) NOT NULL,
category VARCHAR(20) NOT NULL,
highpriority TINYINT(1) NOT NULL,
task VARCHAR(300) NOT NULL,
due VARCHAR(10) NOT NULL,
completed TINYINT(1) NOT NULL default \"0\",
datecompleted VARCHAR(12) NOT NULL,
PRIMARY KEY (id)
) ENGINE = MYISAM;";

//Run query
mysql_query($createtable);

//Write Config
$configfile = fopen("../config.php", "w");
fwrite($configfile, $installstring);
fclose($configfile);

mysql_close($con);

?>
<div class="alert alert-success"><h4 class="alert-heading">Install Complete</h4><p>Burden has been successfully installed. Please delete the "installer" folder from your server, as it poses a potential security risk!</p>
<p>Your login details are shown below, please make a note of them.</p>
<ul>
<li>User: <?php echo $adminuser; ?></li>
<li>Password: <?php echo $_POST["adminpassword"]; ?></li>
</ul>
<p><a href="../admin/login.php" class="btn btn-success">Go To Login</a></p>
</div>
</div>
<!-- Content end -->
<!-- Javascript start -->	
<script src="../resources/jquery.min.js"></script>
<script src="../resources/bootstrap/js/bootstrap.min.js"></script>
<!-- Javascript end -->
</body>
</html>