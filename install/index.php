<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden

require_once("../assets/version.php");

//Check if Burden has been installed
if (file_exists("../config.php") && (!isset($_GET["nonce"]))) {
    die("Information: Burden has already been installed! To reinstall the app please delete your config file and run this installer again.");
}

//Make sure we start at step 0
if (!isset($_GET["step"])) {
    header("Location: ?step=0");
    exit;
}

//Stop bad things from happening
$step = $_GET["step"];
$steps = array("0", "1", "2", "3");
if (!in_array($step, $steps)) {
    $step = "0";
}

//Nonce to check against
$nonce = md5(date("h"));

//Check nonce if step 0
if (($step == "0") && (isset($_GET["nonce"]))) {
    if ($nonce != $_GET["nonce"]) {
        header("Location: ?step=0");
        exit;
    }
}

//Create config.php
if (isset($_POST["step_1"])) {
    
    $dbhost = $_POST["dbhost"];
    $dbuser = $_POST["dbuser"];
    $dbpassword = $_POST["dbpassword"];
    $dbname = $_POST["dbname"];
    
    //Check if we can connect
    @$con = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname);
    if (mysqli_connect_errno()) {
        die("Error: Could not connect to database (" . mysqli_connect_error() . "). Check your database settings are correct.");
    }
    
    $installstring = "<?php\n\n//Database Settings\ndefine('DB_HOST', " . var_export($dbhost, true) . ");\ndefine('DB_USER', " . var_export($dbuser, true) . ");\ndefine('DB_PASSWORD', " . var_export($dbpassword, true) . ");\ndefine('DB_NAME', " . var_export($dbname, true) . ");\n\n//Other Settings\ndefine('VERSION', " . var_export($version, true) . ");\n\n?>";

    //Write Config
    $configfile = fopen("../config.php", "w");
    fwrite($configfile, $installstring);
    fclose($configfile);
    
    //Generate nonce
    $nonce = md5(date("i") * 5);
    header("Location: index.php?step=2&nonce=$nonce");
    exit;

}

//Create tables
if (isset($_POST["step_2"])) {
    
    require_once("../config.php");

    $user = $_POST["user"];
    $email = $_POST["email"];
    if (empty($_POST["password"])) {
        die("Error: No  password set.");
    } else {
        //Salt and hash passwords
        $randsalt = md5(uniqid(rand(), true));
        $salt = substr($randsalt, 0, 3);
        $hashedpassword = hash("sha256", $_POST["password"]);
        $password = hash("sha256", $salt . $hashedpassword);
    }
    $api_key = substr(str_shuffle(MD5(microtime())), 0, 50);
    
    //Check if we can connect
    @$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if (mysqli_connect_errno()) {
        die("Error: Could not connect to database (" . mysqli_connect_error() . "). Check your database settings are correct.");
    }

    //Create Data table
    $createdatatable = "CREATE TABLE `Data` (
    `id` smallint(10) NOT NULL AUTO_INCREMENT,
    `category` varchar(20) NOT NULL,
    `highpriority` tinyint(1) NOT NULL,
    `task` varchar(300) NOT NULL,
    `details` varchar(300) NOT NULL,
    `created` date NOT NULL,
    `due` date NOT NULL,
    `completed` tinyint(1) NOT NULL DEFAULT \"0\",
    `datecompleted` date NOT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM;";
    
    mysqli_query($con, $createdatatable);
    
    //Create Users table
    $createuserstable = "CREATE TABLE `Users` (
    `id` smallint(10) NOT NULL AUTO_INCREMENT,
    `user` varchar(20) NOT NULL,
    `password` varchar(200) NOT NULL,
    `salt` varchar(3) NOT NULL,
    `email` varchar(100) NOT NULL,
    `hash` varchar(200) NOT NULl,
    `api_key` varchar(200) NOT NULl,
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM;";
    
    mysqli_query($con, $createuserstable);
    
    //Check if a user already exists
    $checkifuserexists = "SELECT user FROM `Users`;";
    $resultcheckifuserexists = mysqli_query($con, $checkifuserexists);
    if (mysqli_num_rows($resultcheckifuserexists) == 0) {
        //Add user
        mysqli_query($con, "INSERT INTO Users (user, password, salt, email, hash, api_key)
        VALUES (\"$user\",\"$password\",\"$salt\",\"$email\",\"\",\"$api_key\")");
    }
        
    mysqli_close($con);
    
    //Generate nonce
    $nonce = md5(date("h"));
    header("Location: index.php?step=3&nonce=$nonce&user=$user");
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Burden &middot; Install</title>
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
if ($step == "0") {    
?>
<p>Welcome to Burden <?php echo $version ?>. Before getting started, we need some information on your database. You will need to know the following items before proceeding.</p>
<ul>
<li>Database name</li>
<li>Database username</li>
<li>Database password</li>
<li>Database host</li>
</ul>
<p>You will then be asked to create an admin user.</p>
<p>Click "Install" to get started!</p>
<a href="?step=1&amp;nonce=<?php echo $nonce; ?>" class="btn btn-primary pull-right" role="button">Install</a>
<?php   
} elseif (($step == "1") && ($_GET["nonce"] == $nonce)) {
?>
<form role="form" method="post" autocomplete="off">
<h4>Database Settings</h4>
<div class="form-group">
<label for="dbhost">Database Host</label>
<input type="text" class="form-control" id="dbhost" name="dbhost" value="localhost" placeholder="Type your database host..." required>
</div>
<div class="form-group">
<label for="dbuser">Database User</label>
<input type="text" class="form-control" id="dbuser" name="dbuser" placeholder="Type your database user..." required>
</div>
<div class="form-group">
<label for="dbpassword">Database Password</label>
<input type="password" class="form-control" id="dbpassword" name="dbpassword" placeholder="Type your database password..." required>
</div>
<div class="form-group">
<label for="dbname">Database Name</label>
<input type="text" class="form-control" id="dbname" name="dbname" placeholder="Type your database name..." required>
</div>
<input type="hidden" name="step_1">
<input type="submit" class="btn btn-default pull-right" value="Next">
</form>
<?php
} elseif (($step == "2") && ($_GET["nonce"] == $nonce)) {
?>
<form role="form" method="post" autocomplete="off">
<h4>User Details</h4>
<div class="form-group">
<label for="user">User</label>
<input type="text" class="form-control" id="user" name="user" placeholder="Type a username..." required>
</div>
<div class="form-group">
<label for="email">Email</label>
<input type="email" class="form-control" id="email" name="email" placeholder="Type an email..." required>
</div>
<div class="form-group">
<label for="password">Password</label>
<input type="password" class="form-control" id="password" name="password" placeholder="Type a password..." required>
</div>
<div class="form-group">
<label for="passwordconfirm">Confirm Password</label>
<input type="password" class="form-control" id="passwordconfirm" name="passwordconfirm" placeholder="Type your password again..." required>
<span class="help-block">It is recommended that your password be at least 6 characters long</span>
</div>
<input type="hidden" name="step_2">
<input type="submit" class="btn btn-default pull-right" value="Finish">
</form>
<?php
} elseif (($step == "3") && ($_GET["nonce"] == $nonce)) {   
?>
<div class="alert alert-success">
<h4 class="alert-heading">Install Complete</h4>
<p>Burden has been successfully installed. Please delete the "install" folder from your server, as it poses a potential security risk!</p>
<p>Your login details are shown below, please make a note of them.</p>
<ul>
<li>User: <i><?php echo strip_tags($_GET["user"]); ?></i></li>
<li>Password: <i>Password you set during install</i></li></ul>
</div>
<a href="../login.php" class="btn btn-default pull-right" role="button">Login</a>
<?php
} else {
?>
<div class="alert alert-danger">
<h4 class="alert-heading">Install Error</h4>
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
<script src="../assets/nod.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    var metrics = [
        ["#dbhost", "presence", "Database host cannot be empty!"],
        ["#dbuser", "presence", "Database user cannot be empty!"],
        ["#dbpassword", "presence", "Database password cannot be empty!"],        
        ["#dbname", "presence", "Database name cannot be empty!"],
        ["#user", "presence", "User name cannot be empty!"],
        ["#email", "email", "Enter a valid email address"],
        ["#password", "presence", "Passwords should be more than 6 characters"],
        ["#passwordconfirm", "same-as: #password", "Passwords do not match!"]
    ];
    $("form").nod(metrics);
});
</script>
</body>
</html>