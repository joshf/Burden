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

if (!isset($_POST["idtoedit"])) {
    header("Location: ../index.php");
    exit;
}	

//Connect to database
@$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    die("Error: Could not connect to database (" . mysqli_connect_error() . "). Check your database settings are correct.");
}

$idtoedit = mysqli_real_escape_string($con, $_POST["idtoedit"]);

//Set variables
$newtask = mysqli_real_escape_string($con, $_POST["task"]);
$newdetails = mysqli_real_escape_string($con, $_POST["details"]);
$newcategory = mysqli_real_escape_string($con, $_POST["category"]);
if (isset($_POST["priority"])) {
    $newpriority = mysqli_real_escape_string($con, $_POST["priority"]);
}
$newdue = mysqli_real_escape_string($con, $_POST["due"]);

//Failsafes
if (empty($newtask) || empty($newdue)) {
    header("Location: ../edit.php?id=$idtoedit&error=emptyfields");
    exit;
}

if (isset($_POST["highpriority"])) {
    $newhighpriority = "1";
} else {
    $newhighpriority = "0";
}

//Store dates in correct format
if (!isset($_POST["ignoredate"])) {
    $segments = explode("-", $due);
    if (count($segments) == 3) {
        list($day, $month, $year) = $segments;
    }
    $due = "$year-$month-$day";
}

mysqli_query($con, "UPDATE `Data` SET `category` = \"$newcategory\", `highpriority` = \"$newhighpriority\", `task` = \"$newtask\", `details` = \"$newdetails\", `due` = \"$newdue\" WHERE `id` = \"$idtoedit\"");

mysqli_close($con);

header("Location: ../index.php");

exit;

?>