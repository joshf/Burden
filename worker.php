<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

if (!file_exists("config.php")) {
    header("Location: install");
    exit;
}

require_once("config.php");

//Connect to database
@$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    die("Error: Could not connect to database (" . mysqli_connect_error() . "). Check your database settings are correct.");
}

session_start();
if (isset($_POST["api_key"])) {
    $api = mysqli_real_escape_string($con, $_POST["api_key"]);
    if (empty($api)) {
        die("Error: No API key passed!");
    }
    $checkkey = mysqli_query($con, "SELECT `id`, `user` FROM `Users` WHERE `api_key` = \"$api\"");
    $checkkeyresult = mysqli_fetch_assoc($checkkey);
    if (mysqli_num_rows($checkkey) == 0) {
        die("Error: API key is not valid!");
    } else {
        $_SESSION["burden_user"] = $checkkeyresult["id"];
    }
}

if (!isset($_SESSION["burden_user"])) {
    header("Location: login.php");
    exit;
}

$getusersettings = mysqli_query($con, "SELECT `user` FROM `Users` WHERE `id` = \"" . $_SESSION["burden_user"] . "\"");
if (mysqli_num_rows($getusersettings) == 0) {
    session_destroy();
    header("Location: login.php");
    exit;
}
$resultgetusersettings = mysqli_fetch_assoc($getusersettings);

if (isset($_POST["id"])) {
    $id = mysqli_real_escape_string($con, $_POST["id"]);
}

if (isset($_POST["action"])) {
    $action = $_POST["action"];
} else {
	die("Error: No action passed!");
}

//Define variables
if (isset($_POST["task"])) {
    $task = mysqli_real_escape_string($con, $_POST["task"]);
}
if (isset($_POST["details"])) {
    $details = mysqli_real_escape_string($con, $_POST["details"]);
}
if (isset($_POST["category"])) {
    $category = mysqli_real_escape_string($con, $_POST["category"]);
}
if (isset($_POST["due"])) {
    $due = mysqli_real_escape_string($con, $_POST["due"]);
}

if ($action == "add") {
    if (empty($task) || empty($due)) {
        die("Error: Data was empty!");
    }

    if (isset($_POST["highpriority"])) {
        $highpriority = "1";
    } else {
        $highpriority = "0";
    }
    
    $datecheck = "/\d{1,2}\-\d{1,2}\-\d{4}/";
    if (preg_match($datecheck, $due)) {
        $segments = explode("-", $due);
        if (count($segments) == 3) {
            list($day, $month, $year) = $segments;
        }
        $due = "$year-$month-$day";
    }

    mysqli_query($con, "INSERT INTO `Data` (`category`, `highpriority`, `task`, `details`, `created`, `due`, `completed`)
    VALUES (\"$category\",\"$highpriority\",\"$task\",\"$details\",CURDATE(),\"$due\",\"0\")");
} elseif ($action == "edit") {
    if (empty($task) || empty($due)) {
        die("Error: Data was empty!");
    }

    if (isset($_POST["highpriority"])) {
        $highpriority = "1";
    } else {
        $highpriority = "0";
    }

    $datecheck = "/\d{1,2}\-\d{1,2}\-\d{4}/";
    if (preg_match($datecheck, $due)) {
        $segments = explode("-", $due);
        if (count($segments) == 3) {
            list($day, $month, $year) = $segments;
        }
        $due = "$year-$month-$day";
    }

    mysqli_query($con, "UPDATE `Data` SET `category` = \"$category\", `highpriority` = \"$highpriority\", `task` = \"$task\", `details` = \"$details\", `due` = \"$due\" WHERE `id` = \"$id\"");
} elseif ($action == "complete") {
    mysqli_query($con, "UPDATE `Data` SET `completed` = \"1\", `datecompleted` =  CURDATE() WHERE `id` = \"$id\"");
} elseif ($action == "restore") {
    mysqli_query($con, "UPDATE `Data` SET `completed` = \"0\", `datecompleted` = \"\" WHERE `id` = \"$id\"");
} elseif ($action == "delete") {
    mysqli_query($con, "DELETE FROM `Data` WHERE `id` = \"$id\"");
} elseif ($action == "details") {
    $getdetails = mysqli_query($con, "SELECT `task`, `created`, `due`, `details`, `category`, `highpriority` FROM `Data` WHERE `id` = \"$id\"");
    $resultgetdetails = mysqli_fetch_assoc($getdetails);
    
    $arr = array();
    $arr[0] = $resultgetdetails["task"];
    $arr[1] = $resultgetdetails["details"];
    $arr[2] = $resultgetdetails["due"];
    $arr[3] = $resultgetdetails["category"];
    $arr[4] = $resultgetdetails["highpriority"];
    echo json_encode($arr);
} elseif ($action == "generateapikey") {
    $api = substr(str_shuffle(MD5(microtime())), 0, 50);
    mysqli_query($con, "UPDATE `Users` SET `api_key` = \"$api\" WHERE `id` = \"" . $_SESSION["burden_user"] . "\"");
    echo $api;
} else {
    die("Error: Action not recognised!");
}

mysqli_close($con);

?>