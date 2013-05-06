<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

$version = "2.0beta";
$codename = "ElectricElephant";
$rev = "130";

if (!file_exists("../config.php")) {
    header("Location: ../installer");
}

require_once("../config.php");

$uniquekey = UNIQUE_KEY;

session_start();
if (!isset($_SESSION["is_logged_in_" . $uniquekey . ""])) {
    header("Location: login.php");
    exit; 
}

//Set cookie so we dont constantly check for updates
setcookie("burdenhascheckedforupdates", "checkedsuccessfully", time()+604800);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Burden</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
if (THEME == "default") {
    echo "<link href=\"../resources/bootstrap/css/bootstrap.css\" type=\"text/css\" rel=\"stylesheet\">\n";  
} else {
    echo "<link href=\"//netdna.bootstrapcdn.com/bootswatch/2.3.1/" . THEME . "/bootstrap.min.css\" type=\"text/css\" rel=\"stylesheet\">\n";
}
?>
<style type="text/css">
body {
    padding-top: 60px;
}
<?
//Fix broken superhero theme
if (THEME == "superhero") {
    echo "td {\n    color: #5A6A7D;\n}\n";
}
?>
</style>
<link href="../resources/bootstrap/css/bootstrap-responsive.css" type="text/css" rel="stylesheet">
<link href="../resources/datatables/dataTables.bootstrap.css" type="text/css" rel="stylesheet">
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
<a class="btw btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
<span class="icon-bar"></span>
<span class="icon-bar"></span>
<span class="icon-bar"></span>
</a>
<a class="brand" href="#">Burden</a>
<div class="nav-collapse collapse">
<ul class="nav">
<li class="active"><a href="index.php">Home</a></li>
<li class="divider-vertical"></li>
<li><a href="add.php">Add</a></li>
<li><a href="edit.php">Edit</a></li>
</ul>
<ul class="nav pull-right">
<li class="dropdown">
<a href="#" class="dropdown-toggle" data-toggle="dropdown">View Options<b class="caret"></b></a>
<ul class="dropdown-menu">
<li><a href="index.php?showcompleted">Show Completed</a></li>
<li><a href="index.php">Show Current</a></li>
</ul>
</li>
<li><a href="settings.php">Settings</a></li>
<li><a href="logout.php">Logout</a></li>
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

if (!isset($_GET["showcompleted"])) {
    echo "<h1>Current Tasks</h1>";
} else {
    echo "<h1>Completed Tasks</h1>";
}
echo "</div>";		

echo "<noscript><div class=\"alert alert-info\"><h4 class=\"alert-heading\">Information</h4><p>Please enable JavaScript to use Burden. For instructions on how to do this, see <a href=\"http://www.activatejavascript.org\" target=\"_blank\">here</a>.</p></div></noscript>";

@$con = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$con) {
    die("<div class=\"alert alert-error\"><h4 class=\"alert-heading\">Error</h4><p>Could not connect to database (" . mysql_error() . "). Check your database settings are correct.</p><p><a class=\"btn btn-danger\" href=\"javascript:history.go(-1)\">Go Back</a></p></div></div></body></html>");
}

$does_db_exist = mysql_select_db(DB_NAME, $con);
if (!$does_db_exist) {
    die("<div class=\"alert alert-error\"><h4 class=\"alert-heading\">Error</h4><p>Database does not exist (" . mysql_error() . "). Check your database settings are correct.</p><p><a class=\"btn btn-danger\" href=\"javascript:history.go(-1)\">Go Back</a></p></div></div></body></html>");
}

if (!isset($_GET["showcompleted"])) {
    $gettasks = mysql_query("SELECT * FROM Data WHERE completed != \"1\"");
} else {
    $gettasks = mysql_query("SELECT * FROM Data WHERE completed = \"1\"");
}

//Update checking
if (!isset($_COOKIE["burdenhascheckedforupdates"])) {
    $remoteversion = file_get_contents("https://raw.github.com/joshf/Burden/master/version.txt");
    if (preg_match("/^[0-9.-]{1,}$/", $remoteversion)) {
        if ($version < $remoteversion) {
            echo "<div class=\"alert\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button><h4 class=\"alert-heading\">Update</h4><p><a href=\"https://github.com/joshf/Burden/compare/$version...$remoteversion\" target=\"_blank\">Burden $remoteversion</a> is available. <a href=\"https://github.com/joshf/Burden/wiki/Updating-Burden\" target=\"_blank\">Click here to update</a>.</p></div>";
        }
    }
} 

echo "<table id=\"tasks\" class=\"table table-striped table-bordered table-condensed\">
<thead>
<tr>
<th></th>
<th>Category</th>";
if (!isset($_GET["showcompleted"])) {
    echo "<th>Priority</th>";
}
echo "<th>Task</th>";
if (!isset($_GET["showcompleted"])) {
    echo "<th>Due</th>";
} else {
    echo "<th>Date Completed</th>"; 
}
echo "</tr></thead><tbody>";

while($row = mysql_fetch_assoc($gettasks)) {
    //Logic for due date
    $due = $row["due"];
    $today = date("Y-m-d");
    $todaystring = strtotime($today);
    $duestring = strtotime($due);    
    if ($row["priority"] != "5" && $row["completed"] != "1" && $todaystring < $duestring) {
        $case = "normal";
    }
    if ($row["priority"] == "5") {
        if ($todaystring > $duestring) {
            $case = "overdue";
        } else {
            $case = "highpriority";
        }
    } 
    if ($todaystring > $duestring) {
        if ($due == "None") {
            if ($row["priority"] == "5") {
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
    echo "<td>" . ucfirst($row["category"]) . "</td>";
    if (!isset($_GET["showcompleted"])) {
        echo "<td>" . $row["priority"] . "</td>";
    }
    echo "<td>" . $row["task"] . "</td>";
    if (!isset($_GET["showcompleted"])) {
        echo "<td>" . $row["due"] . "</td>";
    } else {
        echo "<td>" . $row["datecompleted"] . "</td>";
    }
    echo "</tr>";
}
echo "</tbody></table>";

?>
<div class="btn-group">
<button id="edit" class="btn">Edit</button>
<button id="delete" class="btn">Delete</button>
<?php

if (!isset($_GET["showcompleted"])) {
    echo "<button id=\"complete\" class=\"btn\">Complete</button>";
} else {
    echo "<button id=\"restore\" class=\"btn\">Restore</button>";
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

$getnumberoftasks = mysql_query("SELECT COUNT(id) FROM Data WHERE completed != \"1\"");
$resultnumberoftasks = mysql_fetch_assoc($getnumberoftasks);
echo "<i class=\"icon-list-alt\"></i> <b>" . $resultnumberoftasks["COUNT(id)"] . "</b> current tasks, ";

$getnumberofcompletedtasks = mysql_query("SELECT COUNT(id) FROM Data WHERE completed = \"1\"");
$resultnumberofcompletedtasks = mysql_fetch_assoc($getnumberofcompletedtasks);
echo "<b>" . $resultnumberofcompletedtasks["COUNT(id)"] . "</b> completed";

mysql_close($con);

?>
</div>
<hr>
<p class="muted pull-right">Burden <? echo $version; ?> (<? echo $rev; ?>) "<? echo $codename; ?>"  &copy; <a href="http://github.com/joshf" target="_blank">Josh Fradley</a> <? echo date("Y"); ?>. Themed by <a href="http://twitter.github.com/bootstrap/" target="_blank">Bootstrap</a>.</p>
</div>
<!-- Content end -->
<!-- Javascript start -->	
<script src="../resources/jquery.js"></script>
<script src="../resources/bootstrap/js/bootstrap.js"></script>
<script src="../resources/datatables/jquery.dataTables.js"></script>
<script src="../resources/datatables/dataTables.bootstrap.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    /* Table selection */
    is_selected = false;
    $("#tasks input[name=id]").click(function() {
        id = $("#tasks input[name=id]:checked").val();
        is_selected = true;
    });
    /* End */
    /* Datatables */
    $("#tasks").dataTable({
        "sDom": "<'row'<'span6'l><'span6'f>r>t<'row'<'span6'i><'span6'p>>",
        "sPaginationType": "bootstrap",
        "aoColumnDefs": [{ 
            "bSortable": false, 
            "aTargets": [0] 
        }] 
    });
    $.extend($.fn.dataTableExt.oStdClasses, {
        "sSortable": "header",
        "sWrapper": "dataTables_wrapper form-inline"
    });
    /* End */
    /* Edit */
    $("#edit").click(function() {
        if (!is_selected) {
            alert("No task selected!");
        } else {
            window.location = "edit.php?id="+ id +"";
        }
    });
    /* End */
    /* Delete */
    $("#delete").click(function() {
        if (!is_selected) {
            alert("No task selected!");
        } else {
            deleteconfirm=confirm("Delete this task?")
            if (deleteconfirm==true) {
                $.ajax({  
                    type: "POST",  
                    url: "actions/worker.php",  
                    data: "action=delete&id="+ id +"",
                    error: function() {  
                        alert("Ajax query failed!");
                    },
                    success: function() {  
                        alert("Task deleted!");
                        window.location.reload();      
                    }	
                });
            } else {
                return false;
            }
        } 
    });
    /* End */
    /* Complete */
    $("#complete").click(function() {
        if (!is_selected) {
            alert("No task selected!");
        } else {
            comconfirm=confirm("Mark this task as completed?")
            if (comconfirm==true) {
                $.ajax({  
                    type: "POST",  
                    url: "actions/worker.php",  
                    data: "action=complete&id="+ id +"",
                    error: function() {  
                        alert("Ajax query failed!");
                    },
                    success: function() {  
                        alert("Task marked as completed!");
                        window.location.reload();      
                    }	
                });
            } else {
                return false;
            }
        } 
    });
    /* End */
    /* Restore */
    $("#restore").click(function() {
        if (!is_selected) {
            alert("No task selected!");
        } else {
            restoreconfirm=confirm("Restore task?")
            if (restoreconfirm==true) {
                $.ajax({  
                    type: "POST",  
                    url: "actions/worker.php",  
                    data: "action=restore&id="+ id +"",
                    error: function() {  
                        alert("Ajax query failed!");
                    },
                    success: function() {  
                        alert("Task restored!");
                        window.location.reload();      
                    }	
                });
            } else {
                return false;
            }
        } 
    });
    /* End */
});
</script>
<!-- Javascript end -->
</body>
</html>