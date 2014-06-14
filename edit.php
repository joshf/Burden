<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

if (!file_exists("config.php")) {
    header("Location: installer");
    exit;
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

$getusersettings = mysqli_query($con, "SELECT `user` FROM `Users` WHERE `id` = \"" . $_SESSION["burden_user"] . "\"");
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
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Burden &middot; Edit</title>
<link href="assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/datepicker/css/datepicker3.min.css" rel="stylesheet">
<link href="assets/bootstrap-select/css/bootstrap-select.min.css" rel="stylesheet">
<style type="text/css">
body {
    padding-top: 30px;
    padding-bottom: 30px;
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
<li><a href="index.php">Home</a></li>
<li><a href="add.php">Add</a></li>
<li class="active"><a href="edit.php">Edit</a></li>
</ul>
<ul class="nav navbar-nav navbar-right">
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
<h1>Edit</h1>
</div>
<?php

//Quick edit selector
if (!isset($_GET["id"])) {
	$getids = mysqli_query($con, "SELECT `id`, `task` FROM `Data` WHERE `completed` = \"0\"");
    if (mysqli_num_rows($getids) != 0) {
        echo "<form role=\"form\" method=\"get\"><div class=\"form-group\"><label for=\"id\">Select a task to edit</label><select class=\"form-control\" id=\"id\" name=\"id\">";
        while($row = mysqli_fetch_assoc($getids)) {
            echo "<option value=\"" . $row["id"] . "\">" . ucfirst($row["task"]) . "</option>";
        }
        echo "</select></div><button type=\"submit\" class=\"btn btn-default\">Select</button></form>";
    } else {
        echo "<div class=\"alert alert-info\"><h4 class=\"alert-heading\">Information</h4><p>No tasks available to edit.</p><p><a class=\"btn btn-info\" href=\"javascript:history.go(-1)\">Go Back</a></p></div>";
    }
} else {

?>
<?php

$idtoedit = mysqli_real_escape_string($con, $_GET["id"]);

//Check if ID exists
$doesidexist = mysqli_query($con, "SELECT `id` FROM `Data` WHERE `id` = $idtoedit");
if (mysqli_num_rows($doesidexist) == 0) {
    echo "<div class=\"alert alert-danger\"><h4 class=\"alert-heading\">Error</h4><p>ID does not exist.</p><p><a class=\"btn btn-danger\" href=\"javascript:history.go(-1)\">Go Back</a></p></div>";
} else {

//Error display
if (isset($_GET["error"])) {
    $error = $_GET["error"];
    if ($error == "emptyfields") {
        echo "<div class=\"alert alert-danger\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\" aria-hidden=\"true\">&times;</button><h4 class=\"alert-heading\">Error</h4><p>One or more fields were left empty.</p></div>";
    }
}
?>
<form role="form" action="actions/edit.php" method="post" autocomplete="off">
<?php

$getidinfo = mysqli_query($con, "SELECT * FROM `Data` WHERE `id` = $idtoedit");
$getidinforesult = mysqli_fetch_assoc($getidinfo);

echo "<div class=\"form-group\"><label for=\"task\">Task</label><input type=\"text\" class=\"form-control\" id=\"task\" name=\"task\" value=\"" . $getidinforesult["task"] . "\" placeholder=\"Type a task...\" required></div>";
echo "<div class=\"form-group\"><label for=\"details\">Details</label><textarea rows=\"2\" class=\"form-control\" id=\"details\" name=\"details\" placeholder=\"Type any extra details..\">" . $getidinforesult["details"] . "</textarea></div>";
echo "<div class=\"form-group\"><label for=\"due\">Due</label><input type=\"text\" class=\"form-control\" id=\"due\" name=\"due\" value=\"" . $getidinforesult["due"] . "\" required></div>";
echo "<div class=\"form-group\"><label for=\"category\">Category</label><select class=\"form-control\" id=\"category\" name=\"category\">";

//Don't duplicate none entry
$doesnoneexist = mysqli_query($con, "SELECT `category` FROM `Data` WHERE `category` = \"none\"");
if (mysqli_num_rows($doesnoneexist) == 0) {
    echo "<option value=\"none\">None</option>";
}

//Get categories
$getcategories = mysqli_query($con, "SELECT DISTINCT(category) FROM `Data` WHERE `category` != \"\"");

while($row = mysqli_fetch_assoc($getcategories)) {    
    if ($row["category"] == $getidinforesult["category"]) {
        echo "<option value=\"" . $row["category"] . "\" selected=\"selected\">" . ucfirst($row["category"]) . "</option>";
    } else {    
        echo "<option value=\"" . $row["category"] . "\">" . ucfirst($row["category"]) . "</option>";
    }
}

echo "</select><span class=\"help-block\"><button type=\"button\" class=\"btn btn-default btn-xs\" id=\"addcategory\">Add Category</button></span></div>";

echo "<div class=\"checkbox\"><label>";
    
//Check if task is high priority
$checkifhighpriority = mysqli_query($con, "SELECT `highpriority` FROM `Data` WHERE `id` = \"$idtoedit\"");
$checkifhighpriorityresult = mysqli_fetch_assoc($checkifhighpriority); 
if ($checkifhighpriorityresult["highpriority"] == "1") { 
    echo "<input type=\"checkbox\" id=\"highpriority\" name=\"highpriority\" checked=\"checked\"> High priority";
} else {
    echo "<input type=\"checkbox\" id=\"highpriority\" name=\"highpriority\"> High priority";
}

mysqli_close($con);

?>
</label>
</div>
<input type="hidden" id="idtoedit" name="idtoedit" value="<?php echo $idtoedit; ?>" />
<button type="submit" class="btn btn-default">Edit</button>
</form>
<?php
}
    }
?>
</div>
<script src="assets/jquery.min.js"></script>
<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/datepicker/js/bootstrap-datepicker.min.js"></script>
<script src="assets/bootbox.min.js"></script>
<script src="assets/bootstrap-select/js/bootstrap-select.min.js"></script>
<script src="assets/nod.min.js"></script>
<?php if (isset($_GET["id"])) { ?>
<script type="text/javascript">
$(document).ready(function() {
    if (!/Android|webOS|iPhone|iPad|iPod|BlackBerry|Chrome/i.test(navigator.userAgent)) {
        $("#due").datepicker({
            format: "dd/mm/yyyy",
            autoclose: "true",
            clearBtn: "true"
        });
    } else {
        $("input#due").removeAttr("type");
        $("input#due").prop("type", "date");
        $("<input>").attr({
            type: "hidden",
            id: "bypass",
            name: "bypass"
        }).appendTo("form");
    }
    $("#addcategory").click(function () {
        bootbox.prompt("Add a category", function(newcategory) {
            if (newcategory != null && newcategory != "") {
                $("#category").append("<option value=\"" + newcategory + "\" selected=\"selected\">" + newcategory + "</option>");
                $("select").selectpicker("render");
                $("select").selectpicker("refresh");
            }
        });
    });
    $("select").selectpicker({
        liveSearch: "true"
    });
    var metrics = [
        ["#task", "presence", "Task cannot be empty"],
        ["#due", "presence", "A due date is required (DD/MM/YYYY)"]
    ];
    $("form").nod(metrics);
});
</script>
<?php } ?>
</body>
</html>