<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

if (!file_exists("../config.php")) {
    die("Error: Config file not found! Please reinstall Burden.");
}

require_once("../config.php");

session_start();
if (!isset($_SESSION["burden_user"])) {
    header("Location: ../login.php");
    exit; 
}

//Connect to database
@$con = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$con) {
    die("Error: Could not connect to database (" . mysql_error() . "). Check your database settings are correct.");
}

mysql_select_db(DB_NAME, $con);

//Set variables
$task = mysql_real_escape_string($_POST["task"]);
$category = mysql_real_escape_string($_POST["category"]);
$priority = mysql_real_escape_string($_POST["priority"]);
$due = mysql_real_escape_string($_POST["due"]);

//Failsafes
if (empty($task) || empty($due)) {
    header("Location: ../add.php?error=emptyfields");
    exit;
}

if (isset($_POST["highpriority"])) {
    $highpriority = "1";
} else {
    $highpriority = "0";
}

mysql_query("INSERT INTO `Data` (`category`, `highpriority`, `task`, `due`, `completed`)
VALUES (\"$category\",\"$highpriority\",\"$task\",\"$due\",\"0\")");

mysql_close($con);

header("Location: ../index.php");

exit;

?>