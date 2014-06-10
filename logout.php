<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

session_start();

unset($_SESSION["burden_user"]);

if (isset($_COOKIE["burden_user_rememberme"])) {
    setcookie("burden_user_rememberme", "", time()-86400);
}

header("Location: login.php?logged_out=true");

exit;

?>