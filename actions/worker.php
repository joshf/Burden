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

if (!isset($_POST["id"])) {
    header("Location: ../index.php");
    exit;
}

//Connect to database
@$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    die("Error: Could not connect to database (" . mysqli_connect_error() . "). Check your database settings are correct.");
}

$id = mysqli_real_escape_string($con, $_POST["id"]);

if (isset($_POST["action"])) {
	$action = $_POST["action"];
} else {
	die("Error: No action passed");
}

if ($action == "complete") {
    mysqli_query($con, "UPDATE `Data` SET `completed` = \"1\", `datecompleted` =  CURDATE() WHERE `id` = \"$id\"");
} elseif ($action == "restore") {
    mysqli_query($con, "UPDATE `Data` SET `completed` = \"0\", `datecompleted` = \"\" WHERE `id` = \"$id\"");
} elseif ($action == "delete") {
    mysqli_query($con, "DELETE FROM `Data` WHERE `id` = \"$id\"");
} elseif ($action == "details") {
    $getdetails = mysqli_query($con, "SELECT `created`, `due`, `details` FROM `Data` WHERE `id` = \"$id\"");
    $resultgetdetails = mysqli_fetch_assoc($getdetails);
    
    $today = strtotime(date("Y-m-d")); 
    $due = strtotime($resultgetdetails["due"]);
    $datediff = abs($today - $due);
    $duein = floor($datediff/(60*60*24));
    
    $segments = explode("-", $resultgetdetails["created"]);
    if (count($segments) == 3) {
        list($year, $month, $day) = $segments;
    }
    $created = "$day-$month-$year";
    
    if ($today > $due) {
        $suffix = "day(s) ago";
    } else {
        $suffix = "day(s)";
    }
    
    $message = "<p><b>Details: </b> " . $resultgetdetails["details"] . "</p>";
    $message .= "<p><b>Created on:</b> $created</p>";
    $message .= "<p><b>Due:</b> $duein $suffix</p>";
    
    echo $message;
}

mysqli_close($con);

?>