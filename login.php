<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

if (!file_exists("config.php")) {
    header("Location: installer");
    exit;
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
    $getuser = mysqli_query($con, "SELECT `id`, `hash` FROM `Users` WHERE `hash` = \"$hash\"");
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
    $userinfo = mysqli_query($con, "SELECT `id`, `user`, `password`, `salt` FROM `Users` WHERE `user` = \"$username\"");
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
            mysqli_query($con, "UPDATE `Users` SET `hash` = \"$hash\" WHERE `id` = \"" . $userinforesult["id"] . "\"");
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Burden &middot; Login</title>
<meta name="robots" content="noindex, nofollow">
<link href="assets/bootstrap/css/bootstrap.min.css" type="text/css" rel="stylesheet">
<style type="text/css">
body {
    padding-top: 40px;
    padding-bottom: 40px;
    background-color: #eee;
}
.form-signin {
    max-width: 300px;
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
.form-signin input[type="text"], .form-signin input[type="password"] {
    font-size: 16px;
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
<form role="form" class="form-signin" method="post">
<div class="text-center"><img src="assets/icon.png" width="75" height="75" alt="Burden Logo"></div>
<?php 
if (isset($_GET["login_error"])) {
    echo "<div class=\"alert alert-danger\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>Incorrect login.</div>";
} elseif (isset($_GET["logged_out"])) {
    echo "<div class=\"alert alert-success\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>Successfully logged out.</div>";
}
?>
<div class="form-group">
<label for="username">Username</label>
<input type="text" class="form-control" id="username" name="username" placeholder="Username..." autofocus>
</div>
<div class="form-group">
<label for="password">Password</label>
<input type="password" class="form-control" id="password" name="password" placeholder="Password...">
</div>
<div class="checkbox">
<label>
<input type="checkbox" id="rememberme" name="rememberme"> Remember me
</label>
</div>
<div class="btn-group pull-right">
<a href="reset.php" class="btn btn-default" role="button">Forgotten</a>
<button type="submit" class="btn btn-primary">Login</button>
</div>
</form>
</div>
<script src="assets/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
<?php
} else {
    header("Location: index.php");
    exit;
}
?>