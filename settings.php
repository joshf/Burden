<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

if (!file_exists("config.php")) {
    die("Error: Config file not found!");
}

require_once("config.php");

session_start();
if (!isset($_SESSION["burden_user"])) {
    header("Location: login.php");
    exit;
} 

//Connect to database
@$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    die("Error: Could not connect to database (" . mysqli_connect_error() . "). Check your database settings are correct.");
}

$getusersettings = mysqli_query($con, "SELECT `user`, `password`, `email`, `salt`, `api_key` FROM `users` WHERE `id` = \"" . $_SESSION["burden_user"] . "\"");
if (mysqli_num_rows($getusersettings) == 0) {
    session_destroy();
    header("Location: login.php");
    exit;
}
$resultgetusersettings = mysqli_fetch_assoc($getusersettings);

if (!empty($_POST)) {
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

    //Update Settings
    mysqli_query($con, "UPDATE `users` SET `user` = \"$user\", `password` = \"$password\", `email` = \"$email\", `salt` = \"$salt\" WHERE `user` = \"" . $resultgetusersettings["user"] . "\"");
    
    //Show updated values
    header("Location: settings.php");
    
    exit;
}

mysqli_close($con);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="assets/favicon.ico">
<title>Burden &raquo; Settings</title>
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
<div class="pull-right"><a href="settings.php"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span></a> <a href="logout.php"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span></a></div>
<h1>Settings</h1>
<ol class="breadcrumb">
<li><a href="index.php">Burden</a></li>
<li class="active">Settings</li>
</ol>
<form id="settingsform" method="post" autocomplete="off">
<div class="form-group">
<label class="control-label" for="user">User</label>
<input type="text" class="form-control" id="user" name="user" value="<?php echo $resultgetusersettings["user"]; ?>" placeholder="Enter a username..." required>
</div>
<div class="form-group">
<label class="control-label" for="email">Email</label>
<input type="email" class="form-control" id="email" name="email" value="<?php echo $resultgetusersettings["email"]; ?>" placeholder="Type an email..." required>
</div>
<div class="form-group">
<label class="control-label" for="password">Password</label>
<input type="password" class="form-control" id="password" name="password" value="<?php echo $resultgetusersettings["password"]; ?>" placeholder="Enter a password..." required>
</div>
<button type="submit" id="submit" class="btn btn-default">Update</button>
</form>
<hr>
<h2>API key</h2>
<p>Your API key is: <b><span id="api_key"><?php echo $resultgetusersettings["api_key"]; ?></span></b></p>
<button id="generateapikey" class="btn btn-default">Generate New Key</button>
</div>
<script src="assets/bower_components/jquery/dist/jquery.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/bootstrap/dist/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/bootstrap-validator/dist/validator.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/remarkable-bootstrap-notify/dist/bootstrap-notify.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/js-cookie/src/js.cookie.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/nod/nod.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">
$(document).ready(function() {
    if (Cookies.get("burden_settings_updated")) {
        $.notify({
            message: "Settings updated!",
            icon: "glyphicon glyphicon-ok",
        },{
            type: "success",
            allow_dismiss: true
        });
        Cookies.remove("burden_settings_updated");
    }
    var addval = nod();  
    addval.configure({
        submit: "#submit",
        disableSubmit: true,
        delay: 1000,
        parentClass: "form-group",
        successClass: "has-success",
        errorClass: "has-error",
        successMessageClass: "text-success",
        errorMessageClass: "text-danger"
    });
    addval.add([{
        selector: "#user",
        validate: "presence",
        errorMessage: "User cannot be empty!",
        initialStatus: "valid"
    }, {
        selector: "#email",
        validate: "presence",
        errorMessage: "Email cannot be empty!",
        initialStatus: "valid"
    
    }, {
        selector: "#password",
        validate: "presence",
        errorMessage: "Password cannot be empty!",
        initialStatus: "valid"
    }]);
    $("form").submit(function() {
        Cookies.set("burden_settings_updated", "1", { expires: 7 });
    });
    $("#generateapikey").click(function() {
        $.ajax({
            type: "POST",
            url: "worker.php",
            data: "action=generateapikey",
            error: function() {
                $("#api_key").html("Could not generate key. Failed to connect to worker.</b>");
            },
            success: function(api_key) {
                $("#api_key").html(api_key);
            }
        });
    });
});
</script>
</body>
</html>