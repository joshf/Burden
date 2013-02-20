<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

if (!file_exists("../../config.php")) {
    header("Location: ../../installer");
}

require_once("../../config.php");

$uniquekey = UNIQUE_KEY;

session_start();
if (!isset($_SESSION["is_logged_in_" . $uniquekey . ""])) {
    header("Location: ../login.php");
    exit; 
}

if (!isset($_POST["idtoedit"])) {
    header("Location: ../../admin");
}
	
//Connect to database
@$con = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$con) {
    header("Location: " . $_SERVER["HTTP_REFERER"] . "&error=dberror");
    exit;
}

mysql_select_db(DB_NAME, $con);

$idtoedit = mysql_real_escape_string($_POST["idtoedit"]);

//Set variables
$newtask = mysql_real_escape_string($_POST["task"]);
$newdue = mysql_real_escape_string($_POST["due"]);
$newcategory = mysql_real_escape_string($_POST["category"]);

//Failsafes
if (empty($newtask)) {
    header("Location: " . $_SERVER["HTTP_REFERER"] . "&error=taskempty");
    exit;
}

if (isset($_POST["importantstate"])) {
    $newimportantstate = "1";
} else {
    $newimportantstate = "0";
}

//Allow a blank date
if (empty($newdue)) {
    $newdue = "None";
}

mysql_query("UPDATE Data SET category = \"$newcategory\", task = \"$newtask\", due = \"$newdue\", important = \"$newimportantstate\" WHERE id = \"$idtoedit\"");

mysql_close($con);

header("Location: ../../admin");

?>