<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

if (!file_exists("config.php")) {
    die("Error: Config file not found!");
}

require_once("config.php");

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
    $checkinfo = mysqli_query($con, "SELECT `user`, `id`, `email` FROM `users` WHERE `email` = \"$email\" AND `hash` = \"$hash\"");
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
    mysqli_query($con, "UPDATE `users` SET `password` = \"$password\", `salt` = \"$salt\", `hash` = \"\" WHERE `id` = \"" . $checkinforesult["id"] . "\"");
    
    //Send new pass email
	$to = $checkinforesult["email"];
    $subject = "Burden » Your New Password";
	$headers = "MIME-Version: 1.0\r\n";
    $headers .= "From: Burden Mailer <burden@" . $_SERVER["SERVER_NAME"] . ">\r\n";    
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
    $message = "<html><body><p>Hi " . $checkinforesult["user"] . ",</p><p>Your new password is <b>$rawpassword</b>.</p><p>Click <a href=\"$pathtoscript/login.php\">here</a> to go to the login page.</p><p>Your welcome!<br>- Burden Mailer</p></html></body>";
    if (mail($to, $subject, $message, $headers)) {
        header("Location: reset.php?sent_pass_confirm=true");
    } else {
        header("Location: reset.php?send_fail=true");
    }
    exit;
}

//Check email and send link
if (isset($_POST["email"])) {
    $email = mysqli_real_escape_string($con, $_POST["email"]);
    $userinfo = mysqli_query($con, "SELECT `user`, `email`, `id` FROM `users` WHERE `email` = \"$email\"");
    $userinforesult = mysqli_fetch_assoc($userinfo);
    if (mysqli_num_rows($userinfo) == 0) {
        header("Location: reset.php?email_error=true");
        exit;
    }
    
    //Generate temporary hash and store in database
    $hash = substr(str_shuffle(MD5(microtime())), 0, 50);
    mysqli_query($con, "UPDATE `users` SET `hash` = \"$hash\" WHERE `id` = \"" . $userinforesult["id"] . "\"");
    	
    //Send reset email
	$to = $userinforesult["email"];
    $subject = "Burden » Reset Password";
	$headers = "MIME-Version: 1.0\r\n";
    $headers .= "From: Burden Mailer <burden@" . $_SERVER["SERVER_NAME"] . ">\r\n";    
	$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
    $message = "<html><body><p>Hi " . $userinforesult["user"] . ",</p><p>You have requested to reset your Burden password, to do so click <a href=\"$pathtoscript/reset.php?email=$to&hash=$hash\">here</a> and a new password will be emailed to you.</p><p>If you did not initiate this request, simply ignore this email.</p><p>Your welcome!<br>- Burden Mailer</p></html></body>";
    if (mail($to, $subject, $message, $headers)) {
        header("Location: reset.php?sent_reset_confirm=true");
    } else {
        header("Location: reset.php?send_fail=true");
    }
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="assets/favicon.ico">
<title>Burden &raquo; Reset</title>
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
if (isset($_GET["email_error"])) {
    echo "<div class=\"alert alert-danger\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>Email does not exist.</div>";
} elseif (isset($_GET["sent_reset_confirm"])) {
    echo "<div class=\"alert alert-success\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>A reset link has been sent to your email.</div>";
} elseif (isset($_GET["sent_pass_confirm"])) {
    echo "<div class=\"alert alert-success\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>A new password has been sent to your email.</div>";
} elseif (isset($_GET["send_fail"])) {
    echo "<div class=\"alert alert-danger\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>Message could not be sent.</div>";
} elseif (isset($_GET["hash_error"])) {
    echo "<div class=\"alert alert-danger\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button>Hash error. Link may have already been used.</div>";
} 
?>
<label for="email" class="sr-only">Email</label>
<input type="email" id="email" name="email" class="form-control" placeholder="Email..." required autofocus>
<button class="btn btn-primary btn-block" type="submit">Reset</button>
</form>
</div>
<script src="assets/bower_components/jquery/dist/jquery.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/bootstrap/dist/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
</body>
</html>