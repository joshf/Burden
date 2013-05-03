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
    header("Location: ../edit.php?id=" . $_POST["idtoedit"] . "&error=dberror");
    exit;
}

mysql_select_db(DB_NAME, $con);

$idtoedit = mysql_real_escape_string($_POST["idtoedit"]);

//Set variables
$newtask = mysql_real_escape_string($_POST["task"]);
$newcategory = mysql_real_escape_string($_POST["category"]);
$newpriority = mysql_real_escape_string($_POST["priority"]);
$newdue = mysql_real_escape_string($_POST["due"]);

//Failsafes
if (empty($newtask)) {
    header("Location: ../edit.php?id=$idtoedit&error=taskempty");
    exit;
}

mysql_query("UPDATE Data SET category = \"$newcategory\", priority = \"$newpriority\", task = \"$newtask\", due = \"$newdue\" WHERE id = \"$idtoedit\"");

mysql_close($con);

header("Location: ../../admin");

?>