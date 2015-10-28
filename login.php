<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

if (!file_exists("config.php")) {
    die("Error: Config file not found!");
}

require_once("config.php");

session_start();

//Connect to database
@$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    die("Error: Could not connect to database (" . mysqli_connect_error() . "). Check your database settings are correct.");
}

if (isset($_COOKIE["burden_user_rememberme"])) {
    $hash = $_COOKIE["burden_user_rememberme"];
    $getuser = mysqli_query($con, "SELECT `id`, `hash` FROM `users` WHERE `hash` = \"$hash\"");
    if (mysqli_num_rows($getuser) == 0) {
        header("Location: logout.php");
        exit;
    }
    $userinforesult = mysqli_fetch_assoc($getuser);
    $_SESSION["burden_user"] = $userinforesult["id"];
}

if (isset($_POST["password"]) && isset($_POST["username"])) {
    $username = mysqli_real_escape_string($con, $_POST["username"]);
    $password = mysqli_real_escape_string($con, $_POST["password"]);
    $userinfo = mysqli_query($con, "SELECT `id`, `user`, `password`, `salt` FROM `users` WHERE `user` = \"$username\"");
    $userinforesult = mysqli_fetch_assoc($userinfo);
    if (mysqli_num_rows($userinfo) == 0) {
        header("Location: login.php?login_error=true");
        exit;
    }
    $salt = $userinforesult["salt"];
    $hashedpassword = hash("sha256", $salt . hash("sha256", $password));
    if ($hashedpassword == $userinforesult["password"]) {
        $_SESSION["burden_user"] = $userinforesult["id"];
        if (isset($_POST["rememberme"])) {
            $hash = substr(str_shuffle(MD5(microtime())), 0, 50);
            mysqli_query($con, "UPDATE `users` SET `hash` = \"$hash\" WHERE `id` = \"" . $userinforesult["id"] . "\"");
            setcookie("burden_user_rememberme", $hash, time()+3600*24*7);
        }
    } else {
        header("Location: login.php?login_error=true");
        exit;
    }
}

if (!isset($_SESSION["burden_user"])) {
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="assets/favicon.ico">
<title>Burden &raquo; Login</title>
<link rel="apple-touch-icon" href="assets/icon.png">
<link rel="stylesheet" href="assets/bower_components/bootstrap/dist/css/bootstrap.min.css" type="text/css" media="screen">
<link rel="stylesheet" href="assets/css/burden.css" type="text/css" media="screen">
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>
<div class="container">
<form method="post" class="form-signin">
<img class="logo-img" src="assets/icon.png" alt="Burden">
<?php 
if (isset($_GET["login_error"])) {
    echo "<div class=\"alert alert-danger\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>Incorrect login.</div>";
} elseif (isset($_GET["logged_out"])) {
    echo "<div class=\"alert alert-success\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>Successfully logged out.</div>";
}
?>
<label for="username" class="sr-only">Username</label>
<input type="text" id="username" name="username" class="form-control" placeholder="Username..." required autofocus>
<label for="password" class="sr-only">Password</label>
<input type="password" id="password" name="password" class="form-control" placeholder="Password..." required>
<div class="checkbox">
<label>
<input type="checkbox" value="remember-me"> Remember me
</label>
<a class="pull-right btn btn-default btn-xs" href="reset.php">Reset</a>
</div>
<button class="btn btn-primary btn-block" type="submit">Sign in</button>
</form>
</div>
<script src="assets/bower_components/jquery/dist/jquery.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/bootstrap/dist/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
</body>
</html>
<?php
} else {
    header("Location: index.php");
    exit;
}
?>