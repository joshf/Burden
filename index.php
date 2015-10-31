<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

require_once("assets/version.php");

if (!file_exists("config.php")) {
    die("Error: Config file not found!");
}

require_once("config.php");

session_start();
if (!isset($_SESSION["burden_user"])) {
    header("Location: login.php");
    exit;
}

//Connect to database
@$con = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if (mysqli_connect_errno()) {
    die("Error: Could not connect to database (" . mysqli_connect_error() . "). Check your database settings are correct.");
}

$getusersettings = mysqli_query($con, "SELECT `user` FROM `users` WHERE `id` = \"" . $_SESSION["burden_user"] . "\"");
if (mysqli_num_rows($getusersettings) == 0) {
    session_destroy();
    header("Location: login.php");
    exit;
}
$resultgetusersettings = mysqli_fetch_assoc($getusersettings);

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" href="assets/favicon.ico">
<title>Burden</title>
<link rel="apple-touch-icon" href="assets/icon.png">
<link rel="stylesheet" href="assets/bower_components/bootstrap/dist/css/bootstrap.min.css" type="text/css" media="screen">
<link rel="stylesheet" href="assets/css/burden.css" type="text/css" media="screen">
<link rel="stylesheet" href="assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker3.min.css" type="text/css" media="screen">
<!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>
<div class="container">
<div class="pull-right"><a href="settings.php"><span class="glyphicon glyphicon-cog" aria-hidden="true"></span></a> <a href="logout.php"><span class="glyphicon glyphicon-log-out" aria-hidden="true"></span></a></div>
<h1>Burden</h1>
<ol class="breadcrumb">
<li><a href="index.php">Burden</a></li>
<?php

if (isset($_GET["filter"])) {
    $filter = mysqli_real_escape_string($con, $_GET["filter"]);
    //Prevent bad strings from messing with sorting
    $filters = array("categories", "normal", "highpriority", "completed", "date", "duetoday");
    if (!in_array($filter, $filters)) {
        $filter = "normal";
    }
    //Make sure cat exists
	if ($filter == "categories") {
		if (isset($_GET["cat"])) {
		    $cat = mysqli_real_escape_string($con, $_GET["cat"]);
		    $checkcatexists = mysqli_query($con, "SELECT `category` FROM `data` WHERE `category` = \"$cat\"");
		    if (mysqli_num_rows($checkcatexists) == 0) {
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
   $filter_name = "Completed";
} elseif ($filter == "highpriority") {
    $filter_name = "High Priority";
} elseif ($filter == "categories") {
    if ($cat != "none") {
        $filter_name = $cat;
    } else {
        $filter_name = "No category";
    }
} elseif ($filter == "date") {
    $filter_name = "Sorted By Date";
} elseif ($filter == "normal") {
   $filter_name = "Current Tasks";
} elseif ($filter == "duetoday") {
    $filter_name = "Due Today";
}

echo "<li class=\"active\">$filter_name</li></ol>";

?>
<div class="row">
<div class="col-md-8"><div class="form-group"><input type="text" class="form-control" id="search" placeholder="Search..."></div>
</div>
<div class="col-md-4">
<div class="form-group">
<select class="form-control" id="filters" name="filters">
<option value="index.php">No Filters</option>
<optgroup label="Filters">
<option value="index.php?filter=highpriority">High Priority Tasks</option>
<option value="index.php?filter=completed">Completed Tasks</option>
<option value="index.php?filter=duetoday">Due Today</option>
</optgroup>
<optgroup label="Categories">
<?php

//Don't duplicate none entry
$doesnoneexist = mysqli_query($con, "SELECT `category` FROM `data` WHERE `category` = \"none\"");
if (mysqli_num_rows($doesnoneexist) == 0) {
    echo "<option value=\"index.php?filter=categories&amp;cat=none\">None</option>";
}

//Get categories
$getcategories = mysqli_query($con, "SELECT DISTINCT(category) FROM `data` WHERE `category` != \"\"");

while($task = mysqli_fetch_assoc($getcategories)) {
        echo "<option value=\"index.php?filter=categories&amp;cat=" . $task["category"] . "\">" . ucfirst($task["category"]) . "</option>";
}

?>
</optgroup>
<optgroup label="Sort">
<option value="index.php?filter=date">Due Date</option>
</optgroup>
</select>
</div>
</div>
</div>
<?php

if ($filter == "completed") {
    $gettasks = mysqli_query($con, "SELECT * FROM `data` WHERE `completed` = \"1\"");
} elseif ($filter == "highpriority") {
    $gettasks = mysqli_query($con, "SELECT * FROM `data` WHERE `highpriority` = \"1\" AND `completed` = \"0\"");
} elseif ($filter == "categories") {
	$gettasks = mysqli_query($con, "SELECT * FROM `data` WHERE `completed` = \"0\" AND `category` = \"$cat\"");
} elseif ($filter == "date") {
	$gettasks = mysqli_query($con, "SELECT * FROM `data` WHERE `completed` = \"0\" ORDER BY `due` ASC");
} elseif ($filter == "duetoday") {
    $gettasks = mysqli_query($con, "SELECT * FROM `data` WHERE `completed` = \"0\" AND `due` = CURDATE()");
} else {
    $gettasks = mysqli_query($con, "SELECT * FROM `data` WHERE `completed` = \"0\"");
}

echo "<ul class=\"list-group\">";
if (mysqli_num_rows($gettasks) != 0) {
    while($task = mysqli_fetch_assoc($gettasks)) {
        //Logic
        $today = strtotime(date("Y-m-d"));
        $due = strtotime($task["due"]);

        //Set cases
        if ($task["highpriority"] == "0" && $task["completed"] != "1" && $today < $due || $today == $due) {
            $case = "normal";
        }
        if ($task["highpriority"] == "1") {
            if ($today > $due) {
                $case = "overdue";
            } else {
                $case = "highpriority";
            }
        }
        if ($today > $due) {
            if ($task["due"] == "") {
                if ($task["highpriority"] == "1") {
                    $case = "highpriority";
                } else {
                    $case = "normal";
                }
            } else {
                $case = "overdue";
            }
        }
        if ($today == $due) {
            $case = "duetoday";
        }
        if ($task["completed"] == "1") {
            $case = "completed";
        }
        switch ($case) {
            case "highpriority":
                $label = "warning";
                break;
            case "overdue":
            $label = "danger";
                break;
            case "completed":
            $label = "success";
                break;
            case "normal":
            $label = "default";
                break;
            case "duetoday":
            $label = "info";
                break;
        }
        if ($filter == "completed") {
            $segments = explode("-", $task["datecompleted"]);
            if (count($segments) == 3) {
                list($year, $month, $day) = $segments;
            }
            $date = "$day-$month-$year";
        } else {
            $segments = explode("-", $task["due"]);
            if (count($segments) == 3) {
                list($year, $month, $day) = $segments;
            }
            $date = "$day-$month-$year";
        }
        echo "<li class=\"list-group-item\" id=\"" . $task["id"] . "\"><span class=\"details\" data-id=\"" . $task["id"] . "\">" . $task["task"] . "</span><div class=\"pull-right\">";
        if ($task["category"] != "none") {
            echo "<span class=\"category hidden-xs label label-primary\" data-category=\"" . $task["category"] . "\">" . $task["category"] . "</span> ";
        } 
        echo "<span class=\"label label-$label\" data-id=\"" . $task["id"] . "\">" . $date . "</span> ";
        
        if ($filter == "completed") {
            echo "<span class=\"delete glyphicon glyphicon-trash\" data-id=\"" . $task["id"] . "\"></span> ";
        } else {
            echo "<span class=\"edit glyphicon glyphicon-edit\" data-id=\"" . $task["id"] . "\"></span> ";
        }
        
        if ($filter == "completed") {
            echo "<span class=\"restore glyphicon glyphicon-repeat\" data-id=\"" . $task["id"] . "\"></span>";
        } else {
            echo "<span class=\"complete glyphicon glyphicon-ok\" data-id=\"" . $task["id"] . "\"></span>";
        }
        echo "</div></li>";
    }
} else {
    echo "<li class=\"list-group-item\">No tasks to show</li>";
}
echo "</ul>";

?>
<button type="button" id="launchaddmodal" class="btn btn-default">Add</button><br><br>
<span class="pull-right text-muted"><small>Version <?php echo $version; ?></small></span>
</div>
<div class="modal fade" id="addformmodal" tabindex="-1" role="dialog" aria-labelledby="addformmodal" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
<h4 class="modal-title" id="addformmodaltitle">Add Task</h4>
</div>
<div class="modal-body">
<form id="addform" autocomplete="off">
<div class="form-group">
<input type="text" class="form-control" id="task" name="task" placeholder="Type a task..." required autofocus>
</div>
<div class="form-group">
<textarea rows="2" class="form-control" id="details" name="details" placeholder="Type any extra details.."></textarea>
</div>
<div class="form-group">
<input type="date" class="due form-control" id="due" name="due" required>
</div>
<div class="form-group ">
<div class="input-group">
<select class="form-control" id="category" name="category">
<?php

//Don't duplicate none entry
$doesnoneexist = mysqli_query($con, "SELECT `category` FROM `data` WHERE `category` = \"none\"");
if (mysqli_num_rows($doesnoneexist) == 0) {
    echo "<option value=\"none\">None</option>";
}

//Get categories
$getcategories = mysqli_query($con, "SELECT DISTINCT(category) FROM `data` WHERE `category` != \"\"");

while($task = mysqli_fetch_assoc($getcategories)) {
        echo "<option value=\"" . $task["category"] . "\">" . ucfirst($task["category"]) . "</option>";
}

?>
</select>
<span id="addcategoryforaddform" class="addcat input-group-addon">
<i class="glyphicon glyphicon-plus"></i>
</span>
</div>
</div>
<div class="checkbox">
<label>
<input type="checkbox" id="highpriority" name="highpriority"> High priority</label>
</div>
<input type="hidden" id="action" name="action" value="add">
</form>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
<button type="button" id="add" class="btn btn-primary">Add</button>
</div>
</div>
</div>
</div>
<div class="modal fade" id="editformmodal" tabindex="-1" role="dialog" aria-labelledby="editformmodal" aria-hidden="true">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
<h4 class="modal-title" id="editformmodaltitle">Edit Task</h4>
</div>
<div class="modal-body">
<form id="editform" autocomplete="off">
<div class="form-group">
<input type="text" class="form-control" id="edittask" name="task" placeholder="Type a task..." required>
</div>
<div class="form-group">
<textarea rows="2" class="form-control" id="editdetails" name="details" placeholder="Type any extra details.."></textarea>
</div>
<div class="form-group">
<input type="date" class="due form-control" id="editdue" name="due" required>
</div>
<div class="form-group ">
<div class="input-group">
<select class="form-control" id="editcategory" name="category">
<?php

//Don't duplicate none entry
$doesnoneexist = mysqli_query($con, "SELECT `category` FROM `data` WHERE `category` = \"none\"");
if (mysqli_num_rows($doesnoneexist) == 0) {
    echo "<option value=\"none\">None</option>";
}

//Get categories
$getcategories = mysqli_query($con, "SELECT DISTINCT(category) FROM `data` WHERE `category` != \"\"");

while($task = mysqli_fetch_assoc($getcategories)) {
        echo "<option value=\"" . $task["category"] . "\">" . ucfirst($task["category"]) . "</option>";
}

?>
</select>
<span id="addcategoryforeditform" class="addcat input-group-addon">
<i class="glyphicon glyphicon-plus"></i>
</span>
</div>
</div>
<div class="checkbox">
<label>
<input type="checkbox" id="edithighpriority" name="highpriority"> High priority</label>
</div>
<input type="hidden" id="editaction" name="action" value="edit">
<input type="hidden" id="editid" name="id"></form>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
<button type="button" id="edit" class="btn btn-primary">Edit</button>
</div>
</div>
</div>
</div>
<script src="assets/bower_components/jquery/dist/jquery.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/bootstrap/dist/js/bootstrap.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/js-cookie/src/js.cookie.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/remarkable-bootstrap-notify/dist/bootstrap-notify.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/modernizr-load/modernizr.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/bootbox.js/bootbox.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js" type="text/javascript" charset="utf-8"></script>
<script src="assets/bower_components/nod/nod.js" type="text/javascript" charset="utf-8"></script>
<script type="text/javascript">  
$(document).ready(function () {
    var burden_version = "<?php echo $version; ?>";
    if (!Cookies.get("burden_didcheckforupdates")) {
        $.getJSON("https://api.github.com/repos/joshf/Burden/releases").done(function(resp) {
            var data = resp[0];
            var burden_remote_version = data.tag_name;
            var url = data.zipball_url;
            if (burden_version < burden_remote_version) {
                bootbox.dialog({
                    message: "Burden " + burden_remote_version + " is available. For more information about this update click <a href=\""+ data.html_url + "\" target=\"_blank\">here</a>. Do you wish to download the update? If you click \"Not Now\" you will be not reminded for another 7 days.",
                    title: "Update Available",
                    buttons: {
                        cancel: {
                            label: "Not Now",
                            callback: function() {
                                Cookies.set("burden_didcheckforupdates", "1", { expires: 7 });
                            }
                        },
                        main: {
                            label: "Download Update",
                            className: "btn-primary",
                            callback: function() {
                                window.location.href = data.zipball_url;
                            }
                        }
                    }
                });
            }
        });
    }
    $("#filters").on("change", function() {
        Cookies.remove("filter");
        var url = $(this).val()
        Cookies.set("filter", url, { expires: 7 });
        window.location.href = url;
    });
    var ol = window.location.href;
    if (ol.indexOf("filter") == -1) {
        Cookies.remove("filter");
    }
    if (Cookies.get("filter")) {
        var filter = Cookies.get("filter");
        $("#filters").val(filter);
    }
    $("#search").keyup(function() {
        $("#search-error").remove();
        var filter = $(this).val();
        var count = 0;
        $(".list-group .list-group-item").each(function() {
            if ($(this).text().search(new RegExp(filter, "i")) < 0) {
                $(this).hide();
            } else {
                $(this).show();
                count++;
            }            
        });
        if (count === 0) {
            $(".list-group").prepend("<li class=\"list-group-item\" id=\"search-error\">No tasks found</li>");
        }
    });
    $("#addformmodal").on("keypress", function(e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            $("#add").trigger("click");
        }
    });
    $("#editformmodal").on("keypress", function(e) {
        if (e.keyCode === 13) {
            e.preventDefault();
            $("#edit").trigger("click");
        }
    });
    if (!Modernizr.inputtypes.date) {
        $(".due").datepicker({
            format: "dd-mm-yyyy",
            autoclose: "true",
            todayHighlight: "true"
        });
    }
    $("#addcategoryforaddform").click(function() {
        bootbox.prompt("Enter a category", function(newcategory) {                
          if (newcategory !== null && newcategory != "") {                                             
              $("#addform select").append("<option value=\"" + newcategory + "\" selected=\"selected\">" + newcategory + "</option>");
          }
         });
    });
    $("#addcategoryforeditform").click(function() {
        bootbox.prompt("Enter a category", function(newcategory) {                
          if (newcategory !== null && newcategory != "") {                                             
              $("#editform select").append("<option value=\"" + newcategory + "\" selected=\"selected\">" + newcategory + "</option>");
          }
         });
    });
    $("#launchaddmodal").click(function() {
        $("#addformmodal").modal();
        var addval = nod();  
        addval.configure({
            submit: "#add",
            disableSubmit: true,
            delay: 1000,
            parentClass: "form-group",
            successClass: "has-success",
            errorClass: "has-error",
            successMessageClass: "text-success",
            errorMessageClass: "text-danger"
        });
        addval.add([{
            selector: "#task",
            validate: "presence",
            errorMessage: "Task cannot be empty!"
        }, {
            selector: "#due",
            validate: "presence",
            errorMessage: "A due date is required (DD-MM-YYYY)!"
        
        }]);
    });
    $("#add").click(function() {
        $.ajax({
            type: "POST",
            url: "worker.php",
            data: $("#addform").serialize(),
            error: function() {
                $.notify({
                    message: "Ajax query failed!",
                    icon: "glyphicon glyphicon-warning-sign",
                },{
                    type: "danger",
                    allow_dismiss: true
                });
            },
            success: function() {
                $.notify({
                    message: "Task added!",
                    icon: "glyphicon glyphicon-ok",
                },{
                    type: "success",
                    allow_dismiss: true
                });
                setTimeout(function() {
                	window.location.reload();
                }, 2000);
                $("#addformmodal").modal("hide");
            }
        });
    });
    $("li").on("click", ".edit", function() {
        var id = $(this).data("id");
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "worker.php",
            data: "action=info&id="+ id +"",
            error: function() {
                $.notify({
                    message: "Ajax query failed!",
                    icon: "glyphicon glyphicon-warning-sign",
                },{
                    type: "danger",
                    allow_dismiss: true
                });
            },
            success: function(resp) {
                $("#edithighpriority").prop("checked", false);
                $("#edittask").val(resp.data[0].task);
                $("#editdetails").val(resp.data[0].details);
                if (!Modernizr.inputtypes.date) {
                    raw = resp.data[0].due.split("-");
                    date = raw[2]+"-"+raw[1]+"-"+raw[0];
                    $("#editdue").val(date);
                } else {
                    $("#editdue").val(resp.data[0].due);
                }
                if (resp.data[0].category != "") {
                    $("#editcategory").val(resp.data[0].category);
                } else {
                    $("#editcategory").val("none"); 
                }
                if (resp.data[0].highpriority == "1") {
                    $("#edithighpriority").prop("checked", true);
                }
                $("#editid").val(id);
            }
        });        
        $("#editformmodal").modal();
        var editval = nod();
        editval.configure({
            submit: "#edit",
            disableSubmit: true,
            delay: 5,
            parentClass: "form-group",
            successClass: "has-success",
            errorClass: "has-error",
            successMessageClass: "text-success",
            errorMessageClass: "text-danger"
        });
        editval.add([{
            selector: "#edittask",
            validate: "presence",
            errorMessage: "Task cannot be empty!",
            initialStatus: "valid"
        }, {
            selector: "#editdue",
            validate: "presence",
            errorMessage: "A due date is required (DD-MM-YYYY)!",
            initialStatus: "valid"
        }]);   
    });
    $("#edit").click(function() {
        $.ajax({
            type: "POST",
            url: "worker.php",
            data: $("#editform").serialize(),
            error: function() {
                $.notify({
                    message: "Ajax query failed!",
                    icon: "glyphicon glyphicon-warning-sign",
                },{
                    type: "danger",
                    allow_dismiss: true
                });
            },
            success: function() {
                $.notify({
                    message: "Task edited!",
                    icon: "glyphicon glyphicon-ok",
                },{
                    type: "success",
                    allow_dismiss: true
                });
                setTimeout(function() {
                	window.location.reload();
                }, 2000);
                $("#editformmodal").modal("hide");
            }
        });
    });
    $("li").on("click", ".category", function() {
        var url = $(this).data("category");
        window.location.href = "?filter=categories&cat=" + url + "";
    });
    $("li").on("click", ".complete", function() {
        var id = $(this).data("id");
        $.ajax({
            type: "POST",
            url: "worker.php",
            data: "action=complete&id="+ id +"",
            error: function() {
                $.notify({
                    message: "Ajax query failed!",
                    icon: "glyphicon glyphicon-warning-sign",
                },{
                    type: "danger",
                    allow_dismiss: true
                });
            },
            success: function() {
                $.notify({
                    message: "Task completed!",
                    icon: "glyphicon glyphicon-ok",
                },{
                    type: "success",
                    allow_dismiss: true
                });
                setTimeout(function() {
                	window.location.reload();
                }, 2000);
            }
        });
    });
    $("li").on("click", ".details", function() {
        var id = $(this).data("id");
        if ($("#detailsitem"+id).length) {
            $("#detailsitem"+id).hide("fast");
            setTimeout(function() {
                $("#detailsitem"+id).remove();
            }, 400);            
            return false;
        }
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "worker.php",
            data: "action=info&id="+ id +"",
            error: function() {
                $.notify({
                    message: "Ajax query failed!",
                    icon: "glyphicon glyphicon-warning-sign",
                },{
                    type: "danger",
                    allow_dismiss: true
                });
            },
            success: function(resp) {
                if (resp.data[0].details == "") {
                    resp.data[0].details = "<i>No details available</i>";
                }
                rawdue = resp.data[0].due.split("-");
                due = rawdue[2]+"-"+rawdue[1]+"-"+rawdue[0];
                rawcreated = resp.data[0].created.split("-");
                created = rawcreated[2]+"-"+rawcreated[1]+"-"+rawcreated[0];                
                $("#"+id).append("<div id=\"detailsitem"+ id +"\" style=\"display: none;\"><br><dl><dt>Details</dt><dd>" + resp.data[0].details +  "</dd><dt>Due</dt><dd>" + due +  "</dd><dt>Created</dt><dd>" + created +  "</dd></dl></div>");
                $("#detailsitem"+id).show("fast");
            }
        });
    });
    $("li").on("click", ".restore", function() {
        var id = $(this).data("id");
        $.ajax({
            type: "POST",
            url: "worker.php",
            data: "action=restore&id="+ id +"",
            error: function() {
                $.notify({
                    message: "Ajax query failed!",
                    icon: "glyphicon glyphicon-warning-sign",
                },{
                    type: "danger",
                    allow_dismiss: true
                });
            },
            success: function() {
                $.notify({
                    message: "Task restored!",
                    icon: "glyphicon glyphicon-ok",
                },{
                    type: "success",
                    allow_dismiss: true
                });
                setTimeout(function() {
                	window.location.reload();
                }, 2000);
            }
        });
    });
    $("li").on("click", ".delete", function() {
        var id = $(this).data("id");
        $.ajax({
            type: "POST",
            url: "worker.php",
            data: "action=delete&id="+ id +"",
            error: function() {
                $.notify({
                    message: "Ajax query failed!",
                    icon: "glyphicon glyphicon-warning-sign",
                },{
                    type: "danger",
                    allow_dismiss: true
                });
            },
            success: function() {
                $.notify({
                    message: "Task deleted!",
                    icon: "glyphicon glyphicon-ok",
                },{
                    type: "success",
                    allow_dismiss: true
                });
                setTimeout(function() {
                	window.location.reload();
                }, 2000);
            }
        });
    });
});
</script>
</body>
</html>