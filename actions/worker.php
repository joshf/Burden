<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

if (!file_exists("../config.php")) {
    die("Error: Config file not found! Please reinstall Burden.");
}

require_once("../config.php");

$uniquekey = UNIQUE_KEY;

session_start();
if (!isset($_SESSION["is_logged_in_" . $uniquekey . ""])) {
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
    die("Could not connect: " . mysql_error());
}

mysql_select_db(DB_NAME, $con);

$id = mysql_real_escape_string($_POST["id"]);

$action = $_POST["action"];

if ($action == "complete") {
    $todaysdate = date("d-m-Y");
    mysql_query("UPDATE Data SET completed = \"1\", datecompleted = \"$todaysdate\" WHERE id = \"$id\"");
} elseif ($action == "restore") {
    mysql_query("UPDATE Data SET completed = \"0\", datecompleted = \"\" WHERE id = \"$id\"");
} elseif ($action == "delete") {
    mysql_query("DELETE FROM Data WHERE id = \"$id\"");
}

mysql_close($con);

?>