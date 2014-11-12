<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden

if (!file_exists("../config.php")) {
    header("Location: index.php");
    exit;
}

require_once("../assets/version.php");

require_once("../config.php");

//Connect to database
@$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    die("Error: Could not connect to database (" . mysqli_connect_error() . "). Check your database settings are correct.");
}

if ($version == VERSION) {
    die("Information: The latest version of Burden is already installed and an upgrade is not required.");
}

//Make sure we start at step 0
if (!isset($_GET["step"])) {
    header("Location: ?step=0");
    exit;
}

//Stop bad things from happening
$step = $_GET["step"];
$steps = array("0", "1", "2");
if (!in_array($step, $steps)) {
    $step = "0";
}

//Run upgrade
if ($step == "1") {
    
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

    //Generate an api_key
    $api_key = substr(str_shuffle(MD5(microtime())), 0, 50);

    //Add to table
    mysqli_query($con, "ALTER TABLE `Users` ADD `api_key` VARCHAR(200) NOT NULL; UPDATE `Users` SET `api_key` = \"$api_key\";");

    mysqli_close($con);

    //Generate nonce
    $nonce = md5(date("i"));
    header("Location: upgrade.php?step=2&nonce=$nonce");
    exit;

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
<link href="../assets/bootstrap/css/bootstrap.min.css" type="text/css" rel="stylesheet">
<style type="text/css">
body {
    padding-top: 40px;
    padding-bottom: 40px;
    background-color: #eee;
}
.install-content {
    max-width: 600px;
    padding: 10px 30px 50px;
    margin: 0 auto 20px;
    background-color: #fff;
    border: 1px solid #e5e5e5;
    -webkit-border-radius: 5px;
    -moz-border-radius: 5px;
    border-radius: 5px;
    -webkit-box-shadow: 0 1px 2px rgba(0,0,0,.05);
    -moz-box-shadow: 0 1px 2px rgba(0,0,0,.05);
    box-shadow: 0 1px 2px rgba(0,0,0,.05);
}
.install-content input[type="text"] {
    font-size: 14px;
    height: auto;
    margin-bottom: 5px;
    padding: 5px 10px;
}
</style>
<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
</head>
<body>
<div class="container">
<div class="install-content">
<div class="text-center"><img src="../assets/icon.png" width="75" height="75" alt="Burden Logo"></div>
<?php
//Nonce to check against
$nonce = md5(date("i"));

if ($step == "0") {    
?>
<p>Welcome to Burden. You have just downloaded the newest version and your config file and database may need an unpgrade.</p>
<p>Click Upgrade to start the upgrade process</p>
<a href="?step=1" class="btn btn-primary pull-right" role="button">Upgrade</a>
<?php   
} elseif (($step == "2") && ($_GET["nonce"] == $nonce)) {
?>
<div class="alert alert-success">
<h4 class="alert-heading">Upgrade Complete</h4>
<p>Burden has been successfully upgraded to version <?php echo $version; ?>.</p>
</div>
<a href="../login.php" class="btn btn-default pull-right" role="button">Login</a>
<?php
} else {
?>
<div class="alert alert-danger">
<h4 class="alert-heading">Upgrade Error</h4>
<p>An error occured. Nonce was not set!</p>
<p>Please go back and try again.</p>
</div>
<a href="?step=0&amp;nonce=<?php echo $nonce; ?>" class="btn btn-default pull-right" role="button">Start Over</a>
<?php
    }
?>
</div>
</div>
<script src="../assets/jquery.min.js"></script>
<script src="../assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>