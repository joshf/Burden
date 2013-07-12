<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Burden &middot; Installer</title>
<meta name="robots" content="noindex, nofollow">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="resources/bootstrap/css/bootstrap.min.css" type="text/css" rel="stylesheet">
<style type="text/css">
body {
    padding-top: 60px;
}
</style>
<link href="resources/bootstrap/css/bootstrap-responsive.min.css" type="text/css" rel="stylesheet">
<!-- Javascript start -->
<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
<script src="resources/jquery.min.js"></script>
<script src="resources/bootstrap/js/bootstrap.min.js"></script>
<script src="resources/validation/jqBootstrapValidation.js"></script>
<script type="text/javascript">
$(document).ready(function() {
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
<a class="brand" href="#">Burden</a>
</div>
</div>
</div>
<!-- Nav end -->
<!-- Content start -->
<div class="container">
<div class="page-header">
<h1>Installer</h1>
</div>
<?php

//Check if we should update
if (!isset($_GET["doupdate"])) {
    if ((file_exists("config.php")) && (!file_exists(".installdone"))) {
        die("<div class=\"alert alert-info\"><h4 class=\"alert-heading\">Update Required</h4><p>Burden has been updated to the latest version but its config and databases need to be updated too.<p><a href=\"install.php?doupdate\" class=\"btn btn-info\">Run Update</a></p></div></div></body></html>");
    }
}

//Check if we are installed
if (file_exists(".installdone")) {
    die("<div class=\"alert alert-info\"><h4 class=\"alert-heading\">Information</h4><p>Burden has already been installed! To reinstall the app please delete your .installdone file and run this installer again.</p><p><a class=\"btn btn-info\" href=\"javascript:history.go(-1)\">Go Back</a></p></div></div></body></html>");
}

if (isset($_GET["doinstall"])) {

    $dbhost = $_POST["dbhost"];
    $dbuser = $_POST["dbuser"];
    $dbpassword = $_POST["dbpassword"];
    $dbname = $_POST["dbname"];
    $adminuser = $_POST["adminuser"];
    if (empty($_POST["adminpassword"])) {
        die("<div class=\"alert alert-error\"><h4 class=\"alert-heading\">Install Failed</h4><p>Error: No admin password set.</p><p><a class=\"btn btn-danger\" href=\"javascript:history.go(-1)\">Go Back</a></p></div></div></body></html>");
    } else {
        //Salt and hash passwords
        $randsalt = md5(uniqid(rand(), true));
        $salt = substr($randsalt, 0, 3);
        $hashedpassword = hash("sha256", $_POST["adminpassword"]);
        $adminpassword = hash("sha256", $salt . $hashedpassword);
    }
    $uniquekey = md5(microtime().rand());

    $installstring = "<?php\n\n//Database Settings\ndefine('DB_HOST', " . var_export($dbhost, true) . ");\ndefine('DB_USER', " . var_export($dbuser, true) . ");\ndefine('DB_PASSWORD', " . var_export($dbpassword, true) . ");\ndefine('DB_NAME', " . var_export($dbname, true) . ");\n\n//Admin Details\ndefine('ADMIN_USER', " . var_export($adminuser, true) . ");\ndefine('ADMIN_PASSWORD', " . var_export($adminpassword, true) . ");\ndefine('SALT', " . var_export($salt, true) . ");\n\n//Other Settings\ndefine('UNIQUE_KEY', " . var_export($uniquekey, true) . ");\ndefine('THEME', 'default');\n\n?>";

    //Check if we can connect
    $con = mysql_connect($dbhost, $dbuser, $dbpassword);
    if (!$con) {
        die("<div class=\"alert alert-error\"><h4 class=\"alert-heading\">Install Failed</h4><p>Error: Could not connect to database (" . mysql_error() . "). Check your database settings are correct.</p><p><a class=\"btn btn-danger\" href=\"javascript:history.go(-1)\">Go Back</a></p></div></div></body></html>");
    }

    //Check if database exists
    $does_db_exist = mysql_select_db($dbname, $con);
    if (!$does_db_exist) {
        die("<div class=\"alert alert-error\"><h4 class=\"alert-heading\">Install Failed</h4><p>Error: Database does not exist (" . mysql_error() . "). Check your database settings are correct.</p><p><a class=\"btn btn-danger\" href=\"javascript:history.go(-1)\">Go Back</a></p></div></div></body></html>");
    }

    //Create Data table
    $createtable = "CREATE TABLE Data (
    id SMALLINT(10) NOT NULL,
    category VARCHAR(20) NOT NULL,
    highpriority TINYINT(1) NOT NULL,
    task VARCHAR(300) NOT NULL,
    due VARCHAR(10) NOT NULL,
    completed TINYINT(1) NOT NULL default \"0\",
    datecompleted VARCHAR(12) NOT NULL,
    PRIMARY KEY (id)
    ) ENGINE = MYISAM;";

    //Run query
    mysql_query($createtable);

    //Write Config
    $configfile = fopen("config.php", "w");
    fwrite($configfile, $installstring);
    fclose($configfile);
    
    //Write installdone
    $installdonefile = fopen(".installdone", "w");
    fwrite($installdonefile, "Burden successfully installed");
    fclose($installdonefile);

    mysql_close($con);
    
    die("<div class=\"alert alert-success\"><h4 class=\"alert-heading\">Install Complete</h4><p>Burden has been successfully installed. Please delete the \"installer\" folder from your server, as it poses a potential security risk!</p><p>Your login details are shown below, please make a note of them.</p><ul><li>User: $adminuser</li><li>Password: <i>Password you set during install</i></li></ul><p><a href=\"../login.php\" class=\"btn btn-success\">Go To Login</a></p></div></div></body></html>");

} elseif (isset($_GET["doupdate"])) {
    
    require_once("config.php");

    $dbhost = DB_HOST;
    $dbuser = DB_USER;
    $dbpassword = DB_PASSWORD;
    $dbname = DB_NAME;
    $adminuser = ADMIN_USER;
    //$adminpassword = ADMIN_PASSWORD;
    //$salt = SALT;
    $uniquekey = UNIQUE_KEY;
    $theme = THEME;

    //Salt and hash passwords
    //From 1.4 --> 1.5
    $temppassword = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz123456789"), 0, 6);
    $randsalt = md5(uniqid(rand(), true));
    $salt = substr($randsalt, 0, 3);
    $hashedpassword = hash("sha256", $temppassword);
    $adminpassword = hash("sha256", $salt . $hashedpassword);
    
    $updatestring = "<?php\n\n//Database Settings\ndefine('DB_HOST', " . var_export($dbhost, true) . ");\ndefine('DB_USER', " . var_export($dbuser, true) . ");\ndefine('DB_PASSWORD', " . var_export($dbpassword, true) . ");\ndefine('DB_NAME', " . var_export($dbname, true) . ");\n\n//Admin Details\ndefine('ADMIN_USER', " . var_export($adminuser, true) . ");\ndefine('ADMIN_PASSWORD', " . var_export($adminpassword, true) . ");\ndefine('SALT', " . var_export($salt, true) . ");\n\n//Other Settings\ndefine('UNIQUE_KEY', " . var_export($uniquekey, true) . ");\ndefine('THEME', 'default');\n\n?>";
    
    //Check if we can connect
    $con = mysql_connect($dbhost, $dbuser, $dbpassword);
    if (!$con) {
        die("<div class=\"alert alert-error\"><h4 class=\"alert-heading\">Update Failed</h4><p>Error: Could not connect to database (" . mysql_error() . "). Check your database settings are correct.</p><p><a class=\"btn btn-danger\" href=\"javascript:history.go(-1)\">Go Back</a></p></div></div></body></html>");
    }

    //Check if database exists
    $does_db_exist = mysql_select_db($dbname, $con);
    if (!$does_db_exist) {
        die("<div class=\"alert alert-error\"><h4 class=\"alert-heading\">Update Failed</h4><p>Error: Database does not exist (" . mysql_error() . "). Check your database settings are correct.</p><p><a class=\"btn btn-danger\" href=\"javascript:history.go(-1)\">Go Back</a></p></div></div></body></html>");
    }

    //Alter Data table
    //From 1.4 --> 1.5
    $altertable = "ALTER TABLE `Data`
    CHANGE `id` `id` SMALLINT(10) NOT NULL,
    CHANGE `category` `category` VARCHAR(20) NOT NULL,
    CHANGE `priority` `highpriority` TINYINT(1) NOT NULL,
    CHANGE `task` `task` VARCHAR(300) NOT NULL,
    CHANGE `due` `due` VARCHAR(10) NOT NULL,
    CHANGE `completed` `completed` TINYINT(1) NOT NULL default \"0\",
    CHANGE `datecompleted` `datecompleted` VARCHAR(12) NOT NULL";

    //Run query
    mysql_query($altertable);

    //Write Config
    $configfile = fopen("config.php", "w");
    fwrite($configfile, $updatestring);
    fclose($configfile);
    
    //Write installdone
    $installdonefile = fopen(".installdone", "w");
    fwrite($installdonefile, "Burden successfully installed");
    fclose($installdonefile);
    
    mysql_close($con);
    
    die("<div class=\"alert alert-success\"><h4 class=\"alert-heading\">Update Complete</h4><p>Burden has been successfully updated. Because Burden 1.5 uses salt password hashing, your password is now <b>$temppassword.</b> Please change it to something more memorable as soon as possible.<p><a href=\"login.php\" class=\"btn btn-success\">Go To Login</a></p></div></div></body></html>");
}

?>	
<form action="?doinstall" method="post">
<fieldset>
<h4>Database Settings</h4>
<div class="control-group">
<label class="control-label" for="dbhost">Database Host</label>
<div class="controls">
<input type="text" id="dbhost" name="dbhost" value="localhost" placeholder="Type your database host..." required>
</div>
</div>
<div class="control-group">
<label class="control-label" for="dbuser">Database User</label>
<div class="controls">
<input type="text" id="dbuser" name="dbuser" placeholder="Type your database user..." required>
</div>
</div>
<div class="control-group">
<label class="control-label" for="dbpassword">Database Password</label>
<div class="controls">
<input type="password" id="dbpassword" name="dbpassword" placeholder="Type your database password..." required>
</div>
</div>
<div class="control-group">
<label class="control-label" for="dbname">Database Name</label>
<div class="controls">
<input type="text" id="dbname" name="dbname" placeholder="Type your database name..." required>
</div>
</div>
<h4>Admin Details</h4>
<div class="control-group">
<label class="control-label" for="adminuser">Admin User</label>
<div class="controls">
<input type="text" id="adminuser" name="adminuser" placeholder="Type a username..." required>
</div>
</div>
<div class="control-group">
<label class="control-label" for="adminpassword">Password</label>
<div class="controls">
<input type="password" id="adminpassword" name="adminpassword" placeholder="Type a password..." required>
</div>
</div>
<div class="control-group">
<label class="control-label" for="adminpasswordconfirm">Confirm Password</label>
<div class="controls">
<input type="password" id="adminpasswordconfirm" name="adminpasswordconfirm" placeholder="Type your password again..." data-validation-match-match="adminpassword" required>
<span class="help-block">It is recommended that your password be at least 6 characters long</span>
</div>
</div>
<div class="form-actions">
<input type="hidden" name="doinstall">
<input type="submit" class="btn btn-primary" value="Install">
</div>
</fieldset>
</form>
</div>
<!-- Content end -->
</body>
</html>