<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden

require_once("../assets/version.php");

//Check if Burden has been installed
if (file_exists("../config.php")) {
    die("Information: Burden has already been installed! You can login <a href=\"../login.php\">here</a> or to reinstall the app please delete your config.php file and run this installer again.");
}

if (isset($_POST["install"])) {
    
    $dbhost = $_POST["dbhost"];
    $dbuser = $_POST["dbuser"];
    $dbpassword = $_POST["dbpassword"];
    $dbname = $_POST["dbname"];
    
    $user = $_POST["user"];
    $email = $_POST["email"];
    if (empty($_POST["password"])) {
        die("Error: No password set.");
    } else {
        //Salt and hash passwords
        $randsalt = md5(uniqid(rand(), true));
        $salt = substr($randsalt, 0, 3);
        $hashedpassword = hash("sha256", $_POST["password"]);
        $password = hash("sha256", $salt . $hashedpassword);
    }
    $api_key = substr(str_shuffle(MD5(microtime())), 0, 50);
    
    //Check if we can connect
    @$con = mysqli_connect($dbhost, $dbuser, $dbpassword, $dbname);
    if (mysqli_connect_errno()) {
        die("Error: Could not connect to database (" . mysqli_connect_error() . "). Check your database settings are correct.");
    }
    
    //Create data table
    $createdatatable = "CREATE TABLE `data` (
	`id` smallint(8) NOT NULL,
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
    
	
    mysqli_query($con, $createdatatable) or die(mysqli_error($con));
    
    //Create users table
    $createuserstable = "CREATE TABLE `users` (
    `id` smallint(8) NOT NULL,
    `user` varchar(20) NOT NULL,
    `password` varchar(200) NOT NULL,
    `salt` varchar(3) NOT NULL,
    `email` varchar(100) NOT NULL,
    `hash` varchar(200) NOT NULl,
    `api_key` varchar(200) NOT NULl,
    PRIMARY KEY (`id`)
    ) ENGINE=MyISAM;";
    
    mysqli_query($con, $createuserstable) or die(mysqli_error($con));

    //Add keys
    mysqli_query($con, "ALTER TABLE `data` ADD PRIMARY KEY (`id`)");
    mysqli_query($con, "ALTER TABLE `users` ADD PRIMARY KEY (`id`)");
    mysqli_query($con, "ALTER TABLE `data` CHANGE `id` `id` INT(8) NOT NULL AUTO_INCREMENT");
    mysqli_query($con, "ALTER TABLE `users` CHANGE `id` `id` INT(8) NOT NULL AUTO_INCREMENT");
    
    mysqli_query($con, "INSERT INTO `users` (user, password, salt, email, hash, api_key)
    VALUES (\"$user\",\"$password\",\"$salt\",\"$email\",\"\",\"$api_key\")");
    
    mysqli_close($con);
    
    $installstring = "<?php\n\n//Database Settings\ndefine('DB_HOST', " . var_export($dbhost, true) . ");\ndefine('DB_USER', " . var_export($dbuser, true) . ");\ndefine('DB_PASSWORD', " . var_export($dbpassword, true) . ");\ndefine('DB_NAME', " . var_export($dbname, true) . ");\n\n?>";

    //Write Config
    $configfile = fopen("../config.php", "w");
    fwrite($configfile, $installstring);
    fclose($configfile);
    
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="../assets/favicon.ico">
<title>Burden &raquo; Install</title>
<link rel="apple-touch-icon" href="../assets/icon.png">
<link rel="stylesheet" href="../assets/bower_components/bootstrap/dist/css/bootstrap.min.css" type="text/css" media="screen">
<link rel="stylesheet" href="../assets/css/burden.css" type="text/css" media="screen">
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>
<nav class="navbar navbar-inverse navbar-fixed-top">
<div class="container-fluid">
<div class="navbar-header">
<a class="navbar-brand" href="index.php">Burden</a>
</div>
</div>
</nav>
<div class="container-fluid top-pad">
<?php

if (isset($_POST["install"])) {    
 
?>
<p>Burden has been successfully installed. Please delete the "install" folder from your server, as it poses a potential security risk!</p>
<a href="../login.php" class="btn btn-default" role="button">Login</a>
<?php

} else {

?>
<div class="alert alert-info">Welcome to Burden <?php echo $version ?>. Before getting started, we need some information on your database and for you to create an admin user.</div>
<form id="installform" method="post" autocomplete="off">
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
<input type="hidden" name="install">
<input type="submit" id="submit" class="btn btn-default" value="Install">
</form>
<br>
<?php
}
?>
</div>
<script src="../assets/bower_components/jquery/dist/jquery.min.js" type="text/javascript" charset="utf-8"></script>
<script src="../assets/bower_components/bootstrap/dist/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script src="../assets/bower_components/nod/nod.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
$(document).ready(function () {
    var installform = nod();
    installform.configure({
        submit: "#submit",
        disableSubmit: true,
        delay: 5,
        parentClass: "form-group",
        successClass: "has-success",
        errorClass: "has-error",
        successMessageClass: "text-success",
        errorMessageClass: "text-danger"
    });
    installform.add([{
        selector: "#dbhost",
        validate: "presence",
        errorMessage: "Database host cannot be empty!",
    }, {
        selector: "#dbuser",
        validate: "presence",
        errorMessage: "Database user cannot be empty!",
    }, {
        selector: "#dbpass",
        validate: "presence",
        errorMessage: "Database password cannot be empty!",
    }, {
        selector: "#dbname",
        validate: "presence",
        errorMessage: "Database name cannot be empty!",
    }, {
        selector: "#user",
        validate: "presence",
        errorMessage: "User cannot be empty!",
    }, {
        selector: "#password",
        validate: "presence",
        errorMessage: "Password cannot be empty!",
    }, {
        selector: "#email",
        validate: "email",
        errorMessage: "Email invalid!",
    }]);   
});
</script>
</body>
</html>