<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

if (!file_exists("config.php")) {
    die("Error: Config file not found! Please reinstall Burden.");
}

require_once("config.php");

session_start();

unset($_SESSION["user"]);

if (isset($_COOKIE["burdenrememberme"])) {
	setcookie("burdenrememberme", "", time()-86400);
}

header("Location: login.php?logged_out=true");

exit;

?>