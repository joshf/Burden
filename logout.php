<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

session_start();

unset($_SESSION["burden_user"]);

header("Location: login.php?logged_out=true");

exit;

?>