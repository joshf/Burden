<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden

//Check if Burden has been installed
if (file_exists("../config.php")) {
    die("Information: Burden has already been installed! To reinstall the app please delete your config file and run this installer again.");
}

require_once("../assets/version.php");

if (isset($_POST["install"])) {

    $dbhost = $_POST["dbhost"];
    $dbuser = $_POST["dbuser"];
    $dbpassword = $_POST["dbpassword"];
    $dbname = $_POST["dbname"];
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
    
    $installstring = "<?php\n\n//Database Settings\ndefine('DB_HOST', " . var_export($dbhost, true) . ");\ndefine('DB_USER', " . var_export($dbuser, true) . ");\ndefine('DB_PASSWORD', " . var_export($dbpassword, true) . ");\ndefine('DB_NAME', " . var_export($dbname, true) . ");\n\n//Other Settings\ndefine('VERSION', " . var_export($version, true) . ");\n\n?>";

    //Check if we can connect
    $con = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname);
    if (mysqli_connect_errno()) {
        echo "Error: Could not connect to database (" . mysqli_connect_error() . "). Check your database settings are correct.";
        exit();
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
    `user` varchar(20) NOT NULL,
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
    `admin` tinyint(1) NOT NULL,
    `hash` varchar(200) NOT NULl,
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM;";
    
    mysqli_query($con, $createuserstable);
    
    //Add user
    mysqli_query($con, "INSERT INTO Users (user, password, salt, email, admin, hash)
    VALUES (\"$user\",\"$password\",\"$salt\",\"$email\",\"1\",\"\")");

    //Write Config
    $configfile = fopen("../config.php", "w");
    fwrite($configfile, $installstring);
    fclose($configfile);

    mysqli_close($con);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Burden &middot; Installer</title>
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
<h1>Installer</h1>
</div>
<?php
if (!isset($_POST["install"])) {
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
<input type="hidden" name="install">
<input type="submit" class="btn btn-default" value="Install">
</form>
<?php
} else {
    echo "<div class=\"alert alert-success\"><h4 class=\"alert-heading\">Install Complete</h4><p>Burden has been successfully installed. Please delete the \"installer\" folder from your server, as it poses a potential security risk!</p><p>Your login details are shown below, please make a note of them.</p><ul><li>User: $user</li><li>Password: <i>Password you set during install</i></li></ul><p><a href=\"../login.php\" class=\"btn btn-success\">Go To Login</a></p></div>";
}
?>
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