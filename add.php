<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

if (!file_exists("config.php")) {
    header("Location: installer");
}

require_once("config.php");

$uniquekey = UNIQUE_KEY;

session_start();
if (!isset($_SESSION["is_logged_in_" . $uniquekey . ""])) {
    header("Location: login.php");
    exit; 
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Burden &middot; Add</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
if (THEME == "default") {
    echo "<link href=\"resources/bootstrap/css/bootstrap.css\" type=\"text/css\" rel=\"stylesheet\">\n";  
} else {
    echo "<link href=\"//netdna.bootstrapcdn.com/bootswatch/2.3.1/" . THEME . "/bootstrap.min.css\" type=\"text/css\" rel=\"stylesheet\">\n";
}
?>
<style type="text/css">
body {
    padding-top: 60px;
}
</style>
<link href="resources/bootstrap/css/bootstrap-responsive.css" type="text/css" rel="stylesheet">
<link href="resources/datepicker/css/datepicker.css" type="text/css" rel="stylesheet">
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
<li><a href="index.php">Home</a></li>
<li class="divider-vertical"></li>
<li class="active"><a href="add.php">Add</a></li>
<li><a href="edit.php">Edit</a></li>
</ul>
<ul class="nav pull-right">
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
<h1>Add</h1>
</div>
<?php

//Connect to database
@$con = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$con) {
    die("<div class=\"alert alert-error\"><h4 class=\"alert-heading\">Error</h4><p>Could not connect to database (" . mysql_error() . "). Check your database settings are correct.</p><p><a class=\"btn btn-danger\" href=\"javascript:history.go(-1)\">Go Back</a></p></div></div></body></html>");
}

mysql_select_db(DB_NAME, $con);

//Error display
if (isset($_GET["error"])) {
    $error = $_GET["error"];
    if ($error == "dberror") {
        echo "<div class=\"alert alert-error\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button><h4 class=\"alert-heading\">Error</h4><p>Your task could not be added. Check your database settings or website configuration is correct.</p></div>";
    } elseif ($error == "taskempty") {
        echo "<div class=\"alert alert-error\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button><h4 class=\"alert-heading\">Error</h4><p>Task was empty.</p></div>";
    } elseif ($error == "idexists") {
        echo "<div class=\"alert alert-error\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button><h4 class=\"alert-heading\">Error</h4><p>Task already exists.</p></div>";
    }
}

?>
<form action="actions/add.php" method="post">
<fieldset>
<div class="control-group">
<label class="control-label" for="task">Task</label>
<div class="controls">
<input type="text" id="task" name="task" placeholder="Type a task..." required>
</div>
</div>
<div class="control-group">
<label class="control-label" for="due">Due</label>
<div class="controls">
<input type="text" id="due" name="due" placeholder="Type a due date..." pattern="((((0?[1-9]|[12]\d|3[01])[\.\-\/](0?[13578]|1[02])[\.\-\/]((1[6-9]|[2-9]\d)?\d{2}))|((0?[1-9]|[12]\d|30)[\.\-\/](0?[13456789]|1[012])[\.\-\/]((1[6-9]|[2-9]\d)?\d{2}))|((0?[1-9]|1\d|2[0-8])[\.\-\/]0?2[\.\-\/]((1[6-9]|[2-9]\d)?\d{2}))|(29[\.\-\/]0?2[\.\-\/]((1[6-9]|[2-9]\d)?(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)|00)))|(((0[1-9]|[12]\d|3[01])(0[13578]|1[02])((1[6-9]|[2-9]\d)?\d{2}))|((0[1-9]|[12]\d|30)(0[13456789]|1[012])((1[6-9]|[2-9]\d)?\d{2}))|((0[1-9]|1\d|2[0-8])02((1[6-9]|[2-9]\d)?\d{2}))|(2902((1[6-9]|[2-9]\d)?(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)|00))))" data-validation-pattern-message="Please enter a valid date. Use DD-MM-YYYY.">
</div>
</div>
<div class="control-group">
<label class="control-label" for="category">Category</label>
<div class="controls">
<select id="category" name="category">
<?php

//Don't duplicate none entry
$doesnoneexist = mysql_query("SELECT category FROM Data WHERE category = \"none\"");
$doesnoneexistresult = mysql_fetch_assoc($doesnoneexist); 
if ($doesnoneexistresult == 0) {
    echo "<option value=\"none\">None</option>";
}

//Get categories
$getcategories = mysql_query("SELECT DISTINCT(category) FROM Data WHERE category != \"\"");

while($row = mysql_fetch_assoc($getcategories)) {    
    echo "<option value=\"" . $row["category"] . "\">" . ucfirst($row["category"]) . "</option>";
}

?>
</select>
<span class="help-block"><a id="addcategory">Add new...</a></span>
</div>
</div>
<div class="control-group">
<label class="control-label" for="priority">Priority</label>
<div class="controls">
<select id="priority" name="priority">
<option value="1">1</option>
<option value="2">2</option>
<option value="3">3</option>
<option value="4">4</option>
<option value="5">5</option>
</select>
</div>
</div>
<div class="form-actions">
<button type="submit" class="btn btn-primary">Add</button>
</div>
</fieldset>
</form>
</div>
<!-- Content end -->
<!-- Javascript start -->
<script src="resources/jquery.js"></script>
<script src="resources/bootstrap/js/bootstrap.js"></script>
<script src="resources/datepicker/js/bootstrap-datepicker.js"></script>
<script src="resources/validation/jqBootstrapValidation.js"></script>
<script type="text/javascript">
$(document).ready(function() {
    $("#due").datepicker({
        format: "dd-mm-yyyy",
        autoclose: "true",
        clearBtn: "true"
    });
    $("input").not("[type=submit]").jqBootstrapValidation();
    $("#addcategory").click(function () {
        newcategory=prompt("Add a new category","");
        if (newcategory != null && newcategory != "") {
            $("#category").append("<option value=" + newcategory + " selected=\"selected\">" + newcategory + "</option>");
        }
    });
});
</script>
<!-- Javascript end -->
</body>
</html>