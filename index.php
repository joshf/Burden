<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

$version = "1.8";

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

$getusersettings = mysql_query("SELECT `user`, `theme` FROM `Users` WHERE `id` = \"" . $_SESSION["burden_user"] . "\"");
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
<title>Burden</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="resources/bootstrap/css/bootstrap.min.css" type="text/css" rel="stylesheet">
<?php
if ($resultgetusersettings["theme"] == "dark") { 
    echo "<link href=\"resources/bootstrap/css/darkstrap.min.css\" type=\"text/css\" rel=\"stylesheet\">\n";  
}
?>
<link href="resources/bootstrap/css/bootstrap-responsive.min.css" type="text/css" rel="stylesheet">
<link href="resources/datatables/jquery.dataTables-bootstrap.min.css" type="text/css" rel="stylesheet">
<link href="resources/bootstrap-notify/css/bootstrap-notify.min.css" type="text/css" rel="stylesheet">
<style type="text/css">
body {
    padding-top: 60px;
}
@media (max-width: 980px) {
    body {
        padding-top: 0;
    }
}
</style>
<!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
<!--[if lt IE 9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
</head>
<body>
<!-- Nav start -->
<div class="navbar navbar-fixed-top">
<div class="navbar-inner">
<div class="container">
<a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
<span class="icon-bar"></span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
</a>
<a class="brand" href="#">Burden</a>
<div class="nav-collapse collapse">
<ul class="nav">
<li class="divider-vertical"></li>
<li class="active"><a href="index.php">Home</a></li>
<li><a href="add.php">Add</a></li>
<li><a href="edit.php">Edit</a></li>
</ul>
<ul class="nav pull-right">
<li class="dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown">Filters <b class="caret"></b></a>
<ul class="dropdown-menu">
<li><a href="index.php?filter=highpriority">High Priority Tasks</a></li>
<li><a href="index.php?filter=completed">Completed Tasks</a></li>
<li class="divider"></li>
<li class="nav-header">Categories</li>
<?php

//Get categories
$getcategories = mysql_query("SELECT DISTINCT(category) FROM `Data` WHERE `category` != \"\" AND `completed` != \"1\"");

echo "<li><a href=\"index.php?filter=categories&amp;cat=none\">None</a></li>";

while($row = mysql_fetch_assoc($getcategories)) {
    echo "<li><a href=\"index.php?filter=categories&amp;cat=" . $row["category"] . "\">" . ucfirst($row["category"]) . "</a></li>";
}    

?>
<li class="divider"></li>
<li><a href="index.php">Clear Filters</a></li>
</ul>
</li>
<li class="divider-vertical"></li>
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
</div>
<!-- Nav end -->
<!-- Content start -->
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

echo "<noscript><div class=\"alert alert-info\"><h4 class=\"alert-heading\">Information</h4><p>Please enable JavaScript to use Burden. For instructions on how to do this, see <a href=\"http://www.activatejavascript.org\" target=\"_blank\">here</a>.</p></div></noscript>";

//Update checking
if (!isset($_COOKIE["burdenhascheckedforupdates"])) {
    $remoteversion = file_get_contents("https://raw.github.com/joshf/Burden/master/version.txt");
    if (preg_match("/^[0-9.-]{1,}$/", $remoteversion)) {
        if ($version < $remoteversion) {
            echo "<div class=\"alert\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button><h4 class=\"alert-heading\">Update</h4><p>Burden <a href=\"https://github.com/joshf/Burden/releases/$remoteversion\" target=\"_blank\">$remoteversion</a> is available. <a href=\"https://github.com/joshf/Burden#updating\" target=\"_blank\">Click here to update</a>.</p></div>";
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

echo "<table id=\"tasks\" class=\"table table-striped table-bordered table-condensed\">
<thead>
<tr>
<th></th>
<th class=\"hidden-phone\">Category</th>
<th>Task</th>";
if ($filter == "completed") {
    echo "<th>Date Completed</th>"; 
} else {
    echo "<th>Due</th>"; 
}
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
            echo "<tr class=\"error\">";
            break;
        case "completed":
            echo "<tr class=\"success\">";
            break;
        case "normal":
            echo "<tr>";
            break;
    } 
    echo "<td><input name=\"id\" type=\"radio\" value=\"" . $row["id"] . "\"></td>";
    echo "<td class=\"hidden-phone\">" . ucfirst($row["category"]) . "</td>";
    echo "<td>" . $row["task"] . "</td>";
    if ($filter == "completed") {
        echo "<td>" . $row["datecompleted"] . "</td>";
    } else {
        echo "<td>" . $row["due"] . "</td>";
    }
    echo "</tr>";
}
echo "</tbody></table>";

?>
<div class="btn-group">
<button id="edit" class="btn">Edit</button>
<button id="details" class="btn">Details</button>
<button id="delete" class="btn">Delete</button>
<?php
if ($filter == "completed") {
    echo "<button id=\"restore\" class=\"btn\">Restore</button>";
} else {
    echo "<button id=\"complete\" class=\"btn\">Complete</button>";
}
?>
</div>
<br>
<br>
<div class="alert alert-info">   
<strong>Info:</strong> High priority tasks are highlighted yellow, completed tasks green and overdue tasks red.  
</div>
<div class="well">
<?php

echo "<i class=\"icon-tasks\"></i> <b>$numberoftasks</b> tasks<br><i class=\"icon-warning-sign\"></i> <b>$numberoftasksduetoday</b> due today<br><i class=\"icon-exclamation-sign\"></i> <b>$numberoftasksoverdue</b> overdue";

mysql_close($con);

?>
</div>
<hr>
<p class="muted pull-right">Burden <?php echo $version; ?> &copy; <a href="http://github.com/joshf" target="_blank">Josh Fradley</a> <?php echo date("Y"); ?>. Themed by <a href="http://twitter.github.com/bootstrap/" target="_blank">Bootstrap</a>.</p>
</div>
<!-- Content end -->
<!-- Javascript start -->
<script src="resources/jquery.min.js"></script>
<script src="resources/bootstrap/js/bootstrap.min.js"></script>
<script src="resources/datatables/jquery.dataTables.min.js"></script>
<script src="resources/datatables/jquery.dataTables-bootstrap.min.js"></script>
<script src="resources/bootstrap-notify/js/bootstrap-notify.min.js"></script>
<script src="resources/bootbox.min.js"></script>
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
    /* Table selection */
    id_selected = false;
    $("#tasks input[name=id]").click(function() {
        id = $("#tasks input[name=id]:checked").val();
        id_selected = true;
    });
    /* End */
    /* Datatables */
    $("#tasks").dataTable({
        "sDom": "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
        "sPaginationType": "bootstrap",
        "aoColumns": [
            {"bSortable": false},
            null,
            null,
            {"sType": "date-uk"}
        ]
    });
    $.extend($.fn.dataTableExt.oStdClasses, {
        "sSortable": "header",
        "sWrapper": "dataTables_wrapper form-inline"
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
    /* Edit */
    $("#edit").click(function() {
        if (id_selected == true) {
            window.location = "edit.php?id="+ id +"";
        } else {
            show_notification("info", "info-sign", "No ID selected!");
        }
    });
    /* End */
    /* Delete */
    $("#delete").click(function() {
        if (id_selected == true) {
            bootbox.confirm("Are you sure you want to delete this task?", "No", "Yes", function(result) {
                if (result == true) {
                    $.ajax({
                        type: "POST",
                        url: "actions/worker.php",
                        data: "action=delete&id="+ id +"",
                        error: function() {
                            show_notification("error", "warning-sign", "Ajax query failed!");
                        },
                        success: function() {
                            show_notification("success", "ok", "Task deleted!", true);
                        }
                    });
                }
            });
        } else {
            show_notification("info", "info-sign", "No ID selected!");
        }
    });
    /* End */
    /* Details */
    $("#details").click(function() {
        if (id_selected == true) {
            $.ajax({
                type: "POST",
                url: "actions/worker.php",
                data: "action=details&id="+ id +"",
                error: function() {
                    show_notification("error", "warning-sign", "Ajax query failed!");
                },
                success: function(message) {
                    bootbox.alert(message);
                }
            });
        } else {
            show_notification("info", "info-sign", "No ID selected!");
        }
    });
    /* End */
    /* Complete */
    $("#complete").click(function() {
        if (id_selected == true) {
            $.ajax({
                type: "POST",
                url: "actions/worker.php",
                data: "action=complete&id="+ id +"",
                error: function() {
                    show_notification("error", "warning-sign", "Ajax query failed!");
                },
                success: function() {
                    show_notification("success", "ok", "Task marked as completed!", true);
                }
            });
        } else {
            show_notification("info", "info-sign", "No ID selected!");
        }
    });
    /* End */
    /* Restore */
    $("#restore").click(function() {
        if (id_selected == true) {
            $.ajax({
                type: "POST",
                url: "actions/worker.php",
                data: "action=restore&id="+ id +"",
                error: function() {
                    show_notification("error", "warning-sign", "Ajax query failed!");
                },
                success: function() {
                    show_notification("success", "ok", "Task restored!", true);
                }
            });
        } else {
            show_notification("info", "info-sign", "No ID selected!");
        }
    });
    /* End */
    /* Update Title */
    document.title = "Burden (<?php echo $numberoftasks; ?>)";
    /* End */
});
</script>
<!-- Javascript end -->
</body>
</html>