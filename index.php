<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

$version = "2.0dev";

if (!file_exists("config.php")) {
    die("Error: Config file not found! Please reinstall Burden.");
}

require_once("config.php");

session_start();
if (!isset($_SESSION["burden_user"])) {
    header("Location: login.php");
    exit; 
}

//Set cookie so we dont constantly check for updates
setcookie("burdenhascheckedforupdates", "checkedsuccessfully", time()+604800);

@$con = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$con) {
    die("Error: Could not connect to database (" . mysql_error() . "). Check your database settings are correct.");
} else {
    $does_db_exist = mysql_select_db(DB_NAME, $con);
    if (!$does_db_exist) {
        die("Error: Database does not exist (" . mysql_error() . "). Check your database settings are correct.");
    }
}

$getusersettings = mysql_query("SELECT `user` FROM `Users` WHERE `id` = \"" . $_SESSION["burden_user"] . "\"");
if (mysql_num_rows($getusersettings) == 0) {
    session_destroy();
    header("Location: login.php");
    exit;
}
$resultgetusersettings = mysql_fetch_assoc($getusersettings);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Burden</title>
<link rel="apple-touch-icon" href="assets/icon.png">
<link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
<link href="assets/datatables/css/dataTables.bootstrap.min.css" rel="stylesheet">
<link href="assets/bootstrap-notify/css/bootstrap-notify.min.css" rel="stylesheet">
<style type="text/css">
body {
    padding-top: 30px;
    padding-bottom: 30px;
}
/* Fix weird notification appearance */
a.close.pull-right {
    padding-left: 10px;
}
/* Slim down the actions column */
th.sorting_disabled {
    width: 75px;
}
</style>
<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
<![endif]-->
</head>
<body>
<div class="navbar navbar-default navbar-fixed-top" role="navigation">
<div class="container">
<div class="navbar-header">
<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
<span class="sr-only">Toggle navigation</span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
</button>
<a class="navbar-brand" href="#">Burden</a>
</div>
<div class="navbar-collapse collapse">
<ul class="nav navbar-nav">
<li class="active"><a href="index.php">Home</a></li>
<li><a href="add.php">Add</a></li>
<li><a href="edit.php">Edit</a></li>
</ul>
<ul class="nav navbar-nav navbar-right">
<li class="dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown">Filters <b class="caret"></b></a>
<ul class="dropdown-menu">
<li><a href="index.php?filter=highpriority">High Priority Tasks</a></li>
<li><a href="index.php?filter=completed">Completed Tasks</a></li>
<li class="divider"></li>
<li class="dropdown-header">Categories</li>
<?php

echo "<li><a href=\"index.php?filter=categories&amp;cat=none\">None</a></li>";

//Get categories
$getcategories = mysql_query("SELECT DISTINCT(category) FROM `Data` WHERE `category` != \"\" AND `completed` != \"1\"");

while($row = mysql_fetch_assoc($getcategories)) {
    echo "<li><a href=\"index.php?filter=categories&amp;cat=" . $row["category"] . "\">" . ucfirst($row["category"]) . "</a></li>";
}    

?>
<li class="divider"></li>
<li><a href="index.php">Clear Filters</a></li>
</ul>
</li>
<li class="dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown"><?php echo $resultgetusersettings["user"]; ?> <b class="caret"></b></a>
<ul class="dropdown-menu">
<li><a href="settings.php">Settings</a></li>
<li><a href="logout.php">Logout</a></li>
</ul>
</li>
</ul>
</div>
</div>
</div>
<div class="container">
<div class="page-header">
<?php

if (isset($_GET["filter"])) {
    $filter = $_GET["filter"];
    //Prevent bad strings from messing with sorting
    $filters = array("categories", "normal", "highpriority", "completed");
    if (!in_array($filter, $filters)) {
        $filter = "normal";
    }
    //Make sure cat exists
	if ($filter == "categories") {
		if (isset($_GET["cat"])) {
		    $cat = mysql_real_escape_string($_GET["cat"]);
		    $checkcatexists = mysql_query("SELECT `category` FROM `Data` WHERE `category` = \"$cat\"");
		    if (mysql_num_rows($checkcatexists) == 0) {
		        $filter = "normal";
		    }
		} else {
			$filter = "normal";
		}
	}
} else {
    $filter = "normal";
}

if ($filter == "completed") {
    echo "<h1>Completed Tasks</h1>";
} elseif ($filter == "highpriority") {
    echo "<h1>High Priority Tasks</h1>";
} elseif ($filter == "categories") {
    echo "<h1>Tasks in \"$cat\" category</h1>";
} elseif ($filter == "normal") {
    echo "<h1>Current Tasks</h1>";
}
echo "</div><div class=\"notifications top-right\"></div>";		

echo "<noscript><div class=\"alert alert-info\"><h4 class=\"alert-heading\">Information</h4><p>Please enable JavaScript to use Burden. For instructions on how to do this, see <a href=\"http://www.activatejavascript.org\" class=\"alert-link\" target=\"_blank\">here</a>.</p></div></noscript>";

//Update checking
if (!isset($_COOKIE["burdenhascheckedforupdates"])) {
    $remoteversion = file_get_contents("https://raw.github.com/joshf/Burden/master/version.txt");
    if (preg_match("/^[0-9.-]{1,}$/", $remoteversion)) {
        if ($version < $remoteversion) {
            echo "<div class=\"alert alert-warning\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button><h4 class=\"alert-heading\">Update</h4><p>Burden <a href=\"https://github.com/joshf/Burden/releases/$remoteversion\" class=\"alert-link\" target=\"_blank\">$remoteversion</a> is available. <a href=\"https://github.com/joshf/Burden#updating\" class=\"alert-link\" target=\"_blank\">Click here for instructions on how to update</a>.</p></div>";
        }
    }
} 

if ($filter == "completed") {
    $gettasks = mysql_query("SELECT * FROM `Data` WHERE `completed` = \"1\"");
} elseif ($filter == "highpriority") {
    $gettasks = mysql_query("SELECT * FROM `Data` WHERE `highpriority` = \"1\" AND `completed` = \"0\"");
} elseif ($filter == "categories") {
	$gettasks = mysql_query("SELECT * FROM `Data` WHERE `completed` = \"0\" AND `category` = \"$cat\"");
} else {
    $gettasks = mysql_query("SELECT * FROM `Data` WHERE `completed` = \"0\"");
}

echo "<table id=\"tasks\" class=\"table table-bordered table-hover table-condensed\">
<thead>
<tr>
<th>Task</th>
<th class=\"hidden-xs\">Category</th>";
if ($filter == "completed") {
    echo "<th>Date Completed</th>"; 
} else {
    echo "<th>Due</th>"; 
}
echo "<th>Actions</th>"; 
echo "</tr></thead><tbody>";

//Set counters to zero
$numberoftasks = "0"; 
$numberoftasksoverdue = "0"; 
$numberoftasksduetoday = "0";

while($row = mysql_fetch_assoc($gettasks)) {
    //Count tasks
    $numberoftasks++;
    //Logic for due date
    $segments = explode("/", $row["due"]);
    if (count($segments) == 3) {
        list($day, $month, $year) = $segments;
    }
    $dueflipped = "$year-$month-$day";
    $today = strtotime(date("Y-m-d")); 
    $due = strtotime($dueflipped);
    //Counters
    if ($today > $due) { 
        $numberoftasksoverdue++; 
    }
    if ($today == $due) { 
        $numberoftasksduetoday++; 
    }
    //Set cases
    if ($row["highpriority"] == "0" && $row["completed"] != "1" && $today < $due) {
        $case = "normal";
    }
    if ($row["highpriority"] == "1") {
        if ($today > $due) {
            $case = "overdue";
        } else {
            $case = "highpriority";
        }
    } 
    if ($today > $due) {
        if ($row["due"] == "") {
            if ($row["highpriority"] == "1") {
                $case = "highpriority";
            } else {
                $case = "normal";
            }
        } else {
            $case = "overdue";
        }
    }
    if ($row["completed"] == "1") {
        $case = "completed";
    }
    switch ($case) {
        case "highpriority":
            echo "<tr class=\"warning\">";
            break;
        case "overdue":
            echo "<tr class=\"danger\">";
            break;
        case "completed":
            echo "<tr class=\"success\">";
            break;
        case "normal":
            echo "<tr>";
            break;
    } 
    echo "<td>" . $row["task"] . "</td>";
    echo "<td class=\"hidden-xs\">" . ucfirst($row["category"]) . "</td>";
    if ($filter == "completed") {
        echo "<td>" . $row["datecompleted"] . "</td>";
    } else {
        echo "<td>" . $row["due"] . "</td>";
    }
    echo "<td><div class=\"btn-toolbar\" role=\"toolbar\"><div class=\"btn-group\"><a href=\"edit.php?id=" . $row["id"] . "\" class=\"btn btn-default btn-xs\" role=\"button\"><span class=\"glyphicon glyphicon-edit\"></span></a><button type=\"button\" class=\"details btn btn-default btn-xs\" data-id=\"" . $row["id"] . "\"><span class=\"glyphicon glyphicon-question-sign\"></span></button>";
    if ($filter == "completed") {
        echo "<button type=\"button\" class=\"restore btn btn-default btn-xs\" data-id=\"" . $row["id"] . "\"><span class=\"glyphicon glyphicon-repeat\"></span></button>";
    } else {
        echo "<button type=\"button\" class=\"complete btn btn-default btn-xs\" data-id=\"" . $row["id"] . "\"><span class=\"glyphicon glyphicon-ok\"></span></button>";
    }
    echo "<button type=\"button\" class=\"delete btn btn-default btn-xs\" data-id=\"" . $row["id"] . "\"><span class=\"glyphicon glyphicon-trash\"></span></button></div></div></td>";
    echo "</tr>";
}
echo "</tbody></table>";

?>
<div class="alert alert-info">
<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>   
<strong>Info:</strong> High priority tasks are highlighted yellow, completed tasks green and overdue tasks red.  
</div>
<div class="well">
<?php

echo "<i class=\"glyphicon glyphicon-tasks\"></i> <b>$numberoftasks</b> tasks<br><i class=\"glyphicon glyphicon-warning-sign\"></i> <b>$numberoftasksduetoday</b> due today<br><i class=\"glyphicon glyphicon-exclamation-sign\"></i> <b>$numberoftasksoverdue</b> overdue";

mysql_close($con);

?>
</div>
<hr>
<div class="footer">
Burden <?php echo $version; ?> &copy; <a href="http://github.com/joshf" target="_blank">Josh Fradley</a> <?php echo date("Y"); ?>. Themed by <a href="http://twitter.github.com/bootstrap/" target="_blank">Bootstrap</a>.
</div>
</div>
<script src="assets/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/datatables/js/jquery.dataTables.min.js"></script>
<script src="assets/datatables/js/dataTables.bootstrap.min.js"></script>
<script src="assets/bootbox.min.js"></script>
<script src="assets/bootstrap-notify/js/bootstrap-notify.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    /* Set Up Notifications */
    var show_notification = function(type, icon, text, reload) {
        $(".top-right").notify({
            type: type,
            transition: "fade",
            icon: icon,
            message: {
                text: text
            },
            onClosed: function() {
                if (reload == true) {
                    window.location.reload();
                }
            }
        }).show();
    };
    /* End */
    /* Datatables */
    $("#tasks").dataTable({
        "aoColumns": [
            null,
            null,
            {"sType": "date-uk"},
            {"bSortable": false}
        ]
    });
    $.extend($.fn.dataTableExt.oSort, {
        "date-uk-pre": function (a) {
            var ukDatea = a.split("/");
            return (ukDatea[2] + ukDatea[1] + ukDatea[0]) * 1;
        },
        "date-uk-asc": function (a, b) {
            return ((a < b) ? -1 : ((a > b) ? 1 : 0));
        },
        "date-uk-desc": function (a, b) {
            return ((a < b) ? 1 : ((a > b) ? -1 : 0));
        }
    });
    /* End */
    /* Delete */
    $("table").on("click", ".delete", function() {
        var id = $(this).data("id");
        bootbox.confirm("Are you sure you want to delete this task?", function(result) {
            if (result == true) {
                $.ajax({
                    type: "POST",
                    url: "actions/worker.php",
                    data: "action=delete&id="+ id +"",
                    error: function() {
                        show_notification("danger", "warning-sign", "Ajax query failed!");
                    },
                    success: function() {
                        show_notification("success", "ok", "Task deleted!", true);
                    }
                });
            }
        });
    });
    /* End */
    /* Details */
    $("table").on("click", ".details", function() {
        var id = $(this).data("id");
        $.ajax({
            type: "POST",
            url: "actions/worker.php",
            data: "action=details&id="+ id +"",
            error: function() {
                show_notification("danger", "warning-sign", "Ajax query failed!");
            },
            success: function(message) {
                bootbox.alert(message);
            }
        });
    });
    /* End */
    /* Complete */
    $("table").on("click", ".complete", function() {
        var id = $(this).data("id");
        $.ajax({
            type: "POST",
            url: "actions/worker.php",
            data: "action=complete&id="+ id +"",
            error: function() {
                show_notification("danger", "warning-sign", "Ajax query failed!");
            },
            success: function() {
                show_notification("success", "ok", "Task marked as completed!", true);
            }
        });
    });
    /* End */
    /* Restore */
    $("table").on("click", ".restore", function() {
        var id = $(this).data("id");
        $.ajax({
            type: "POST",
            url: "actions/worker.php",
            data: "action=restore&id="+ id +"",
            error: function() {
                show_notification("danger", "warning-sign", "Ajax query failed!");
            },
            success: function() {
                show_notification("success", "ok", "Task restored!", true);
            }
        });
    });
    /* End */
    /* Update Title */
    document.title = "Burden (<?php echo $numberoftasks; ?>)";
    /* End */
});
</script>
</body>
</html>