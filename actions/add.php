<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

if (!file_exists("../config.php")) {
    header("Location: ../installer");
    exit;
}

require_once("../config.php");

session_start();
if (!isset($_SESSION["burden_user"])) {
    header("Location: ../login.php");
    exit;
}

//Connect to database
@$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    die("Error: Could not connect to database (" . mysqli_connect_error() . "). Check your database settings are correct.");
}

//Set variables
$task = mysqli_real_escape_string($con, $_POST["task"]);
$details = mysqli_real_escape_string($con, $_POST["details"]);
$category = mysqli_real_escape_string($con, $_POST["category"]);
$due = mysqli_real_escape_string($con, $_POST["due"]);

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

//Store dates in correct format
if (!isset($_POST["ignoredate"])) {
    $segments = explode("-", $due);
    if (count($segments) == 3) {
        list($day, $month, $year) = $segments;
    }
    $due = "$year-$month-$day";
}

mysqli_query($con, "INSERT INTO `Data` (`category`, `highpriority`, `task`, `details`, `created`, `due`, `completed`)
VALUES (\"$category\",\"$highpriority\",\"$task\",\"$details\",CURDATE(),\"$due\",\"0\")");

mysqli_close($con);

header("Location: ../index.php");

exit;

?>