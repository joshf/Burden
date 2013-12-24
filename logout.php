<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

if (!file_exists("config.php")) {
    header("Location: installer");
}

require_once("config.php");

session_start();

unset($_SESSION["burden_user"]);

header("Location: login.php?logged_out=true");

exit;

?>