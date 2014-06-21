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

//Get path to Burden
$currenturl = $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
$pathtoscriptwithslash = "http://" . substr($currenturl, 0, strpos($currenturl, "reset.php"));
$pathtoscript = rtrim($pathtoscriptwithslash, "/");	

//Check that passed email and hash are correct
if (isset($_GET["email"]) && isset($_GET["hash"])) {
    $email = mysqli_real_escape_string($con, $_GET["email"]);
    $hash = mysqli_real_escape_string($con, $_GET["hash"]);
    $checkinfo = mysqli_query($con, "SELECT `user`, `id`, `email` FROM `Users` WHERE `email` = \"$email\" AND `hash` = \"$hash\"");
    $checkinforesult = mysqli_fetch_assoc($checkinfo);
    if (mysqli_num_rows($checkinfo) == 0) {
        header("Location: reset.php?hash_error=true");
        exit;
    }
    
    //Generate new password
    $rawpassword = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz0123456789"), 0, 10);
    $randsalt = md5(uniqid(rand(), true));
    $salt = substr($randsalt, 0, 3);    
    $hashedpassword = hash("sha256", $rawpassword);
    $password = hash("sha256", $salt . $hashedpassword);
    mysqli_query($con, "UPDATE `Users` SET `password` = \"$password\", `salt` = \"$salt\", `hash` = \"\" WHERE `id` = \"" . $checkinforesult["id"] . "\"");
    
    //Send new pass email
	$to = $checkinforesult["email"];
    $subject = "New Burden Password";
	$headers = "MIME-Version: 1.0\r\n";
    $headers .= "From: burden@" . $_SERVER["SERVER_NAME"] . "\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
    $message = "<html><body><p>Hi " . $checkinforesult["user"] . ",</p><p>Your new password is <b>$rawpassword</b>.</p><p>Click <a href=\"$pathtoscript/login.php\">here</a> to go to the login page.</p><p>Your welcome!<br>- The Admin</p></html></body>";
    if (mail($to, $subject, $message, $headers)) {
        header("Location: reset.php?sent_pass_confirm=true");
    } else {
        header("Location: reset.php?sent_fail=true");
    }
    exit;
}

//Check email and send link
if (isset($_POST["email"])) {
    $email = mysqli_real_escape_string($con, $_POST["email"]);
    $userinfo = mysqli_query($con, "SELECT `user`, `email`, `id` FROM `Users` WHERE `email` = \"$email\"");
    $userinforesult = mysqli_fetch_assoc($userinfo);
    if (mysqli_num_rows($userinfo) == 0) {
        header("Location: reset.php?email_error=true");
        exit;
    }
    
    //Generate temporary hash and store in database
    $hash = substr(str_shuffle(MD5(microtime())), 0, 50);
    mysqli_query($con, "UPDATE `Users` SET `hash` = \"$hash\" WHERE `id` = \"" . $userinforesult["id"] . "\"");
    	
    //Send reset email
	$to = $userinforesult["email"];
    $subject = "Reset Your Burden Password";
	$headers = "MIME-Version: 1.0\r\n";
    $headers .= "From: burden@" . $_SERVER["SERVER_NAME"] . "\r\n";
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
    $message = "<html><body><p>Hi " . $userinforesult["user"] . ",</p><p>You have requested to reset your Burden password, to do so click <a href=\"$pathtoscript/reset.php?email=$to&hash=$hash\">here</a> and a new password will be emailed to you.</p><p>If you did not initiate this request, simply ignore this email.</p><p>Your welcome!<br>- The Admin</p></html></body>";
    if (mail($to, $subject, $message, $headers)) {
        header("Location: reset.php?sent_reset_confirm=true");
    } else {
        header("Location: reset.php?sent_fail=true");
    }
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Burden &middot; Reset Password</title>
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
.form-signin .form-signin-heading {
    margin-bottom: 10px;
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
if (isset($_GET["email_error"])) {
    echo "<div class=\"alert alert-danger\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>Email does not exist.</div>";
} elseif (isset($_GET["sent_reset_confirm"])) {
    echo "<div class=\"alert alert-success\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>A reset link has been sent to your email.</div>";
} elseif (isset($_GET["sent_pass_confirm"])) {
    echo "<div class=\"alert alert-success\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>A new password has been sent to your email.</div>";
} elseif (isset($_GET["sent_fail"])) {
    echo "<div class=\"alert alert-danger\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>Message could not be sent.</div>";
} elseif (isset($_GET["hash_error"])) {
    echo "<div class=\"alert alert-danger\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>Hash error. Link may have already been used.</div>";
} 
?>
<div class="form-group">
<label for="email">Email</label>
<input type="email" class="form-control" id="email" name="email" placeholder="Email..." autofocus>
</div>
<button type="submit" class="btn btn-primary pull-right">Send Reset Link</button>
</form>
</div>
<script src="assets/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>