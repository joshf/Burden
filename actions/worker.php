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
@$con = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$con) {
    die("Error: Could not connect to database (" . mysql_error() . "). Check your database settings are correct.");
}

mysql_select_db(DB_NAME, $con);

$id = mysql_real_escape_string($_POST["id"]);

if (isset($_POST["action"])) {
	$action = $_POST["action"];
} else {
	die("Error: No action passed");
}

if ($action == "complete") {
    $todaysdate = date("d/m/Y");
    mysql_query("UPDATE `Data` SET `completed` = \"1\", `datecompleted` = \"$todaysdate\" WHERE `id` = \"$id\"");
} elseif ($action == "restore") {
    mysql_query("UPDATE `Data` SET `completed` = \"0\", `datecompleted` = \"\" WHERE `id` = \"$id\"");
} elseif ($action == "delete") {
    mysql_query("DELETE FROM `Data` WHERE `id` = \"$id\"");
} elseif ($action == "details") {
    $getdetails = mysql_query("SELECT `created`, `due`, `details` FROM `Data` WHERE `id` = \"$id\"");
    $resultgetdetails = mysql_fetch_assoc($getdetails);
    
    list($day, $month, $year) = explode("/", $resultgetdetails["due"]);
    $dueflipped = "$year-$month-$day";
    $today = strtotime(date("Y-m-d")); 
    $due = strtotime($dueflipped);
    $datediff = abs($today - $due);
    $duein = floor($datediff/(60*60*24));
    
    if ($today > $due) {
        $suffix = "day(s) ago";
    } else {
        $suffix = "day(s)";
    }
    
    $message = "<p><b>Details: </b> " . $resultgetdetails["details"] . "</p>";
    $message .= "<p><b>Created on:</b> " . $resultgetdetails["created"] . "</p>";
    $message .= "<p><b>Due:</b> $duein $suffix</p>";
    
    echo $message;
}

mysql_close($con);

?>