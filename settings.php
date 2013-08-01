<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

if (!file_exists("config.php")) {
    die("Error: Config file not found! Please reinstall Burden.");
}

require_once("config.php");

session_start();
if (!isset($_SESSION["user"])) {
    header("Location: login.php");
    exit; 
}

//Get current settings
$currentadminuser = ADMIN_USER;
$currentadminpassword = ADMIN_PASSWORD;
$currenttheme = THEME; 

if (isset($_POST["save"])) {
    //Get new settings from POST
    $adminuser = $_POST["adminuser"];
    $adminpassword = $_POST["adminpassword"];
    if ($adminpassword != $currentadminpassword) {
        $hashedpassword = hash("sha256", $adminpassword);
        $adminpassword = hash("sha256", SALT . $hashedpassword);
    }
    $theme = $_POST["theme"];

    $settingsstring = "<?php\n\n//Database Settings\ndefine('DB_HOST', '" . DB_HOST . "');\ndefine('DB_USER', '" . DB_USER . "');\ndefine('DB_PASSWORD', '" . DB_PASSWORD . "');\ndefine('DB_NAME', '" . DB_NAME . "');\n\n//Admin Details\ndefine('ADMIN_USER', " . var_export($adminuser, true) . ");\ndefine('ADMIN_PASSWORD', " . var_export($adminpassword, true) . ");\ndefine('SALT', '" . SALT . "');\n\n//Other Settings\ndefine('THEME', " . var_export($theme, true) . ");\ndefine('VERSION', '" . VERSION . "');\n\n?>";

    //Write config
    $configfile = fopen("config.php", "w");
    fwrite($configfile, $settingsstring);
    fclose($configfile);

    //Show updated values
    header("Location: settings.php");
    
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Burden &middot; Settings</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
if (THEME == "default") {
    echo "<link href=\"resources/bootstrap/css/bootstrap.min.css\" type=\"text/css\" rel=\"stylesheet\">\n";  
} else {
    echo "<link href=\"//netdna.bootstrapcdn.com/bootswatch/2.3.2/" . THEME . "/bootstrap.min.css\" type=\"text/css\" rel=\"stylesheet\">\n";
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
<!-- Javascript start -->
<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="resources/jquery.min.js"></script>
<script src="resources/bootstrap/js/bootstrap.min.js"></script>
<script src="resources/validation/jqBootstrapValidation.min.js"></script>
<script src="resources/bootstrap-notify/js/bootstrap-notify.min.js"></script>
<script src="resources/cookie/jquery.cookie.min.js"></script>
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
<a class="brand" href="index.php">Burden</a>
<div class="nav-collapse collapse">
<ul class="nav">
<li class="divider-vertical"></li>
<li><a href="add.php"><i class="icon-plus-sign"></i> Add</a></li>
<li><a href="edit.php"><i class="icon-edit"></i> Edit</a></li>
</ul>
<ul class="nav pull-right">
<li class="divider-vertical"></li>
<li class="dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="icon-user"></i> <? echo ADMIN_USER; ?> <b class="caret"></b></a>
<ul class="dropdown-menu">
<li><a href="settings.php"><i class="icon-cog"></i> Settings</a></li>
<li><a href="logout.php"><i class="icon-off"></i> Logout</a></li>
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
<h4>Admin Details</h4>
<div class="control-group">
<label class="control-label" for="adminuser">Admin User</label>
<div class="controls">
<input type="text" id="adminuser" name="adminuser" value="<?php echo $currentadminuser; ?>" placeholder="Enter a username..." required>
</div>
</div>
<div class="control-group">
<label class="control-label" for="adminpassword">Admin Password</label>
<div class="controls">
<input type="password" id="adminpassword" name="adminpassword" value="<?php echo $currentadminpassword; ?>" placeholder="Enter a password..." required>
</div>
</div>
<h4>Theme</h4>
<div class="control-group">
<label class="control-label" for="theme">Theme</label>
<div class="controls">
<?php
$themes = array("default", "amelia", "cerulean", "cosmo", "cyborg", "flatly", "journal", "readable", "simplex", "slate", "spacelab", "spruce", "superhero", "united");

echo "<select id=\"theme\" name=\"theme\">";
foreach ($themes as $value) {
    if ($value == $currenttheme) {
        echo "<option value=\"$value\" selected=\"selected\">". ucfirst($value) . "</option>";
    } else {
        echo "<option value=\"$value\">". ucfirst($value) . "</option>";
    }
}
echo "</select>";
?>
</div>
</div>
<div class="form-actions">
<button type="submit" name="save" class="btn btn-primary">Save Changes</button>
</div>
</fieldset>
</form>
</div>
<!-- Content end -->
</body>
</html>