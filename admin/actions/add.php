<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

if (!file_exists("../../config.php")) {
    header("Location: ../../installer");
}

require_once("../../config.php");

$uniquekey = UNIQUE_KEY;

session_start();
if (!isset($_SESSION["is_logged_in_" . $uniquekey . ""])) {
    header("Location: login.php");
    exit; 
}

//Connect to database
@$con = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$con) {
    header("Location: ../add.php?error=dberror");
    exit;
}

mysql_select_db(DB_NAME, $con);

//Set variables
$task = mysql_real_escape_string($_POST["task"]);
$due = mysql_real_escape_string($_POST["due"]);
$category = mysql_real_escape_string($_POST["category"]);

//Failsafe
if (empty($task)) {
    header("Location: ../add.php?error=taskempty");
    exit;
}

//Get new ID
$getlasttasknumber = mysql_query("SELECT MAX(id) FROM Data");
$resultgetlasttasknumber = mysql_fetch_assoc($getlasttasknumber);
$id = ($resultgetlasttasknumber["MAX(id)"] + 1);

//Check if ID exists
$checkid = mysql_query("SELECT id FROM Data WHERE id = \"$id\"");
$resultcheckid = mysql_fetch_assoc($checkid); 
if ($resultcheckid != 0) {
    header("Location: ../add.php?error=idexists");
    exit;
}

if (isset($_POST["importantstate"])) {
    $important = "1";
} else {
    $important = "0";
}

//Allow a blank date
if (empty($due)) {
    $due = "None";
}

mysql_query("INSERT INTO Data (id, category, important, task, due, completed)
VALUES (\"$id\",\"$category\",\"$important\",\"$task\",\"$due\",\"0\")");

mysql_close($con);

header("Location: ../../admin");

?>