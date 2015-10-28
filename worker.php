<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

if (!file_exists("config.php")) {
    die("Error: Config file not found!");
}

require_once("config.php");

//Connect to database
@$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    die("Error: Could not connect to database (" . mysqli_connect_error() . "). Check your database settings are correct.");
}

session_start();
if (isset($_POST["api_key"]) || isset($_GET["api_key"])) {
    if (isset($_POST["api_key"])) {
        $api_key = mysqli_real_escape_string($con, $_POST["api_key"]);
    } elseif (isset($_GET["api_key"])) {
        $api_key = mysqli_real_escape_string($con, $_GET["api_key"]);
    }
    if (empty($api_key)) {
        die("Error: No API key passed!");
    }
    $checkkey = mysqli_query($con, "SELECT `id`, `user` FROM `users` WHERE `api_key` = \"$api_key\"");
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

$getusersettings = mysqli_query($con, "SELECT `user` FROM `users` WHERE `id` = \"" . $_SESSION["burden_user"] . "\"");
if (mysqli_num_rows($getusersettings) == 0) {
    session_destroy();
    header("Location: login.php");
    exit;
}
$resultgetusersettings = mysqli_fetch_assoc($getusersettings);

if (isset($_POST["action"])) {
    $action = $_POST["action"];
} elseif (isset($_GET["action"])) {
    $action = $_GET["action"];
} else {
	die("Error: No action passed!");
}

//Check if ID exists
$actions = array("edit", "delete", "restore", "complete", "info");
if (in_array($action, $actions)) {
    if (isset($_POST["id"]) || isset($_GET["id"])) {
        if (isset($_POST["action"])) {
            $id = mysqli_real_escape_string($con, $_POST["id"]);
        } elseif (isset($_GET["action"])) {
            $id = mysqli_real_escape_string($con, $_GET["id"]);
        }
        $checkid = mysqli_query($con, "SELECT `id` FROM `data` WHERE `id` = $id");        
        if (mysqli_num_rows($checkid) == 0) {
        	die("Error: ID does not exist!");
        }
    } else {
    	die("Error: ID not set!");
    }
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

    mysqli_query($con, "INSERT INTO `data` (`category`, `highpriority`, `task`, `details`, `created`, `due`, `completed`)
    VALUES (\"$category\",\"$highpriority\",\"$task\",\"$details\",CURDATE(),\"$due\",\"0\")");
    
    echo "Info: Task added!";
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

    mysqli_query($con, "UPDATE `data` SET `category` = \"$category\", `highpriority` = \"$highpriority\", `task` = \"$task\", `details` = \"$details\", `due` = \"$due\" WHERE `id` = \"$id\"");
    
    echo "Info: Task edited!";
} elseif ($action == "complete") {
    mysqli_query($con, "UPDATE `data` SET `completed` = \"1\", `datecompleted` = CURDATE() WHERE `id` = \"$id\"");
    
    echo "Info: Task completed!";
} elseif ($action == "restore") {
    mysqli_query($con, "UPDATE `data` SET `completed` = \"0\", `datecompleted` = \"\" WHERE `id` = \"$id\"");
    
    echo "Info: Task restored!";
} elseif ($action == "delete") {
    mysqli_query($con, "DELETE FROM `data` WHERE `id` = \"$id\"");
    
    echo "Info: Task deleted!";
} elseif ($action == "info") {
    
    $getdata = mysqli_query($con, "SELECT * FROM `data` WHERE `id` = \"$id\"");
    
    while($item = mysqli_fetch_assoc($getdata)) {
    
        $data[] = array(
            "task" => $item["task"],
            "details" => $item["details"],
            "due" => $item["due"],
            "category" => $item["category"],
            "highpriority" => $item["highpriority"],
            "created" => $item["created"]
        );
    }
    
    echo json_encode(array("data" => $data));
    
} elseif ($action == "generateapikey") {
    $api = substr(str_shuffle(MD5(microtime())), 0, 50);
    mysqli_query($con, "UPDATE `Users` SET `api_key` = \"$api\" WHERE `id` = \"" . $_SESSION["burden_user"] . "\"");
    echo $api;
} else {
    die("Error: Action not recognised!");
}

mysqli_close($con);

?>