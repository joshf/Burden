<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

if (!file_exists("config.php")) {
    die("Error: Config file not found! Please reinstall Burden.");
}

require_once("config.php");

session_start();
if (!isset($_SESSION["burden_user"])) {
    header("Location: login.php");
    exit; 
} 

//Connect to database
@$con = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$con) {
    die("Error: Could not connect to database (" . mysql_error() . "). Check your database settings are correct.");
}

mysql_select_db(DB_NAME, $con);

$getusersettings = mysql_query("SELECT `user`, `password`, `email`, `salt`, `theme` FROM `Users` WHERE `id` = \"" . $_SESSION["burden_user"] . "\"");
if (mysql_num_rows($getusersettings) == 0) {
    session_destroy();
    header("Location: login.php");
    exit;
}
$resultgetusersettings = mysql_fetch_assoc($getusersettings);

if (isset($_POST["save"])) {
    //Get new settings from POST
    $user = $_POST["user"];
    $password = $_POST["password"];
    $email = $_POST["email"];
    $salt = $resultgetusersettings["salt"];
    if ($password != $resultgetusersettings["password"]) {
        //Salt and hash passwords
        $randsalt = md5(uniqid(rand(), true));
        $salt = substr($randsalt, 0, 3);
        $hashedpassword = hash("sha256", $password);
        $password = hash("sha256", $salt . $hashedpassword);
    }
    $theme = $_POST["theme"];

    //Update Settings
    mysql_query("UPDATE Users SET `user` = \"$user\", `password` = \"$password\", `email` = \"$email\", `salt` = \"$salt\", `theme` = \"$theme\" WHERE `user` = \"" . $resultgetusersettings["user"] . "\"");
    
    //Show updated values
    header("Location: settings.php");
    
    exit;
}

mysql_close($con);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Burden &middot; Settings</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="resources/bootstrap/css/bootstrap.min.css" type="text/css" rel="stylesheet">
<?php
if ($resultgetusersettings["theme"] == "dark") { 
    echo "<link href=\"resources/bootstrap/css/darkstrap.min.css\" type=\"text/css\" rel=\"stylesheet\">\n";  
}
?>
<link href="resources/bootstrap/css/bootstrap-responsive.min.css" type="text/css" rel="stylesheet">
<link href="resources/bootstrap-notify/css/bootstrap-notify.min.css" type="text/css" rel="stylesheet">
<style type="text/css">
body {
	padding-top: 60px;
}
@media (max-width: 980px) {
	body {
		padding-top: 0;
	}
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
<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
<span class="icon-bar"></span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
</a>
<a class="brand" href="#">Burden</a>
<div class="nav-collapse collapse">
<ul class="nav">
<li class="divider-vertical"></li>
<li><a href="index.php">Home</a></li>
<li><a href="add.php">Add</a></li>
<li><a href="edit.php">Edit</a></li>
</ul>
<ul class="nav pull-right">
<li class="divider-vertical"></li>
<li class="dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $resultgetusersettings["user"]; ?> <b class="caret"></b></a>
<ul class="dropdown-menu">
<li><a href="settings.php">Settings</a></li>
<li><a href="logout.php">Logout</a></li>
</ul>
</li>
</ul>
</div>
</div>
</div>
</div>
<!-- Nav end -->
<!-- Content start -->
<div class="container">
<div class="page-header">
<h1>Settings</h1>
</div>
<div class="notifications top-right"></div>
<form method="post" autocomplete="off">
<fieldset>
<h4>User Details</h4>
<div class="control-group">
<label class="control-label" for="user">User</label>
<div class="controls">
<input type="text" id="user" name="user" value="<?php echo $resultgetusersettings["user"]; ?>" placeholder="Enter a username..." required>
</div>
</div>
<div class="control-group">
<label class="control-label" for="email">Email</label>
<div class="controls">
<input type="email" id="email" name="email" value="<?php echo $resultgetusersettings["email"]; ?>" placeholder="Type an email..." required>
</div>
</div>
<div class="control-group">
<label class="control-label" for="password">Password</label>
<div class="controls">
<input type="password" id="password" name="password" value="<?php echo $resultgetusersettings["password"]; ?>" placeholder="Enter a password..." required>
</div>
</div>
<h4>Theme</h4>
<div class="control-group">
<label class="control-label" for="theme">Theme</label>
<div class="controls">
<?php
$themes = array("default", "dark");

echo "<select id=\"theme\" name=\"theme\">";
foreach ($themes as $value) {
    if ($value == $resultgetusersettings["theme"]) {
        echo "<option value=\"$value\" selected=\"selected\">". ucfirst($value) . "</option>";
    } else {
        echo "<option value=\"$value\">". ucfirst($value) . "</option>";
    }
}
echo "</select>";
?>
</div>
</div>
<p>Dark theme created by <a href="https://github.com/danneu/darkstrap" target="_blank">Dan Neumann.</a></p>
<div class="form-actions">
<button type="submit" name="save" class="btn btn-primary">Save Changes</button>
</div>
</fieldset>
</form>
</div>
<!-- Content end -->
<!-- Javascript start -->
<script src="resources/jquery.min.js"></script>
<script src="resources/bootstrap/js/bootstrap.min.js"></script>
<script src="resources/jqBootstrapValidation.min.js"></script>
<script src="resources/bootstrap-notify/js/bootstrap-notify.min.js"></script>
<script src="resources/jquery.cookie.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    if ($.cookie("settings_updated")) {
        $(".top-right").notify({
            type: "info",
            transition: "fade",
            icon: "info-sign",
            message: {
                text: "Settings saved!"
            }
        }).show();
        $.removeCookie("settings_updated");
    }
    $("form").submit(function() {
        $.cookie("settings_updated", "true");
    });
    $("input").not("[type=submit]").jqBootstrapValidation();
});
</script>
<!-- Javascript end -->
</body>
</html>