<?php

//Burden, Copyright Josh Fradley (http://github.com/joshf/Burden)

if (!file_exists("config.php")) {
    header("Location: installer");
    exit;
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
<title>Burden &middot; Edit</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php
if (THEME == "default") {
    echo "<link href=\"resources/bootstrap/css/bootstrap.css\" type=\"text/css\" rel=\"stylesheet\">\n";  
} else {
    echo "<link href=\"//netdna.bootstrapcdn.com/bootswatch/2.3.2/" . THEME . "/bootstrap.min.css\" type=\"text/css\" rel=\"stylesheet\">\n";
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
<li><a href="add.php">Add</a></li>
<li class="active"><a href="edit.php">Edit</a></li>
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
<h1>Edit</h1>
</div>
<?php

//Connect to database
@$con = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
if (!$con) {
    die("<div class=\"alert alert-error\"><h4 class=\"alert-heading\">Error</h4><p>Could not connect to database (" . mysql_error() . "). Check your database settings are correct.</p><p><a class=\"btn btn-danger\" href=\"javascript:history.go(-1)\">Go Back</a></p></div></div></body></html>");
}

$does_db_exist = mysql_select_db(DB_NAME, $con);
if (!$does_db_exist) {
    die("<div class=\"alert alert-error\"><h4 class=\"alert-heading\">Error</h4><p>Database does not exist (" . mysql_error() . "). Check your database settings are correct.</p><p><a class=\"btn btn-danger\" href=\"javascript:history.go(-1)\">Go Back</a></p></div></div></body></html>");
}

//Quick edit selector
if (!isset($_GET["id"])) {
	echo "<form action=\"edit.php\" method=\"get\"><fieldset><div class=\"control-group\"><label class=\"control-label\" for=\"id\">Select a task to edit</label><div class=\"controls\"><select id=\"id\" name=\"id\">";
	$getids = mysql_query("SELECT id, task FROM Data");
	while($row = mysql_fetch_assoc($getids)) {    
    	echo "<option value=\"" . $row["id"] . "\">" . ucfirst($row["task"]) . "</option>";
	}
	echo "</select></div></div><div class=\"form-actions\"><button type=\"submit\" class=\"btn btn-primary\">Edit</button></div></fieldset></form></div></body></html>";
	exit;
}

$idtoedit = mysql_real_escape_string($_GET["id"]);

//Check if ID exists
$doesidexist = mysql_query("SELECT id FROM Data WHERE id = \"$idtoedit\"");
$doesidexistresult = mysql_fetch_assoc($doesidexist); 
if ($doesidexistresult == 0) {
    die("<div class=\"alert alert-error\"><h4 class=\"alert-heading\">Error</h4><p>ID does not exist.</p><p><a class=\"btn btn-danger\" href=\"javascript:history.go(-1)\">Go Back</a></p></div></div></body></html>");
}

//Error display
if (isset($_GET["error"])) {
    $error = $_GET["error"];
    if ($error == "dberror") {
        echo "<div class=\"alert alert-error\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button><h4 class=\"alert-heading\">Error</h4><p>Your task could not be added. Check your database settings or website configuration is correct.</p></div>";
    } elseif ($error == "taskempty") {
        echo "<div class=\"alert alert-error\"><button type=\"button\" class=\"close\" data-dismiss=\"alert\">&times;</button><h4 class=\"alert-heading\">Error</h4><p>Task was empty.</p></div>";
    }
}

?>
<form action="actions/edit.php" method="post">
<fieldset>
<?php

$getidinfo = mysql_query("SELECT * FROM Data WHERE id = \"$idtoedit\"");
while($row = mysql_fetch_assoc($getidinfo)) {
    echo "<div class=\"control-group\"><label class=\"control-label\" for=\"task\">Task</label><div class=\"controls\"><input type=\"text\" id=\"task\" name=\"task\" value=\"" . $row["task"] . "\" placeholder=\"Type a task...\" required></div></div>";
    echo "<div class=\"control-group\"><label class=\"control-label\" for=\"due\">Due</label><div class=\"controls\"><input type=\"text\" id=\"due\" name=\"due\" value=\"" . $row["due"] . "\" placeholder=\"Type a due date...\" pattern=\"((((0?[1-9]|[12]\d|3[01])[\.\-\/](0?[13578]|1[02])[\.\-\/]((1[6-9]|[2-9]\d)?\d{2}))|((0?[1-9]|[12]\d|30)[\.\-\/](0?[13456789]|1[012])[\.\-\/]((1[6-9]|[2-9]\d)?\d{2}))|((0?[1-9]|1\d|2[0-8])[\.\-\/]0?2[\.\-\/]((1[6-9]|[2-9]\d)?\d{2}))|(29[\.\-\/]0?2[\.\-\/]((1[6-9]|[2-9]\d)?(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)|00)))|(((0[1-9]|[12]\d|3[01])(0[13578]|1[02])((1[6-9]|[2-9]\d)?\d{2}))|((0[1-9]|[12]\d|30)(0[13456789]|1[012])((1[6-9]|[2-9]\d)?\d{2}))|((0[1-9]|1\d|2[0-8])02((1[6-9]|[2-9]\d)?\d{2}))|(2902((1[6-9]|[2-9]\d)?(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00)|00))))\" data-validation-pattern-message=\"Please enter a valid date. Use DD-MM-YYYY.\"></div></div>";
    $category = $row["category"];
}
echo "<div class=\"control-group\"><label class=\"control-label\" for=\"category\">Category</label><div class=\"controls\"><select id=\"category\" name=\"category\">";

//Don't duplicate none entry
$doesnoneexist = mysql_query("SELECT category FROM Data WHERE category = \"none\"");
$doesnoneexistresult = mysql_fetch_assoc($doesnoneexist); 
if ($doesnoneexistresult == 0) {
    echo "<option value=\"none\">None</option>";
}

//Get categories
$getcategories = mysql_query("SELECT DISTINCT(category) FROM Data WHERE category != \"\"");

while($row = mysql_fetch_assoc($getcategories)) {    
    if ($row["category"] == $category) {
        echo "<option value=\"" . $row["category"] . "\" selected=\"selected\">" . ucfirst($row["category"]) . "</option>";
    } else {    
        echo "<option value=\"" . $row["category"] . "\">" . ucfirst($row["category"]) . "</option>";
    }
}

echo "</select><span class=\"help-block\"><a id=\"addcategory\">Add new...</a></span></div></div><div class=\"control-group\"><label class=\"control-label\" for=\"priority\">Priority</label><div class=\"controls\">";
    
//Get task priority
$checkpriority = mysql_query("SELECT priority FROM Data WHERE id = \"$idtoedit\"");
$checkpriorityresult = mysql_fetch_assoc($checkpriority); 
$priority = $checkpriorityresult["priority"];

$priorities = array("1", "2", "3", "4", "5");

echo "<select id=\"priority\" name=\"priority\">";
foreach ($priorities as $value) {
    if ($value == $priority) {
        echo "<option value=\"$value\" selected=\"selected\">$value</option>";
    } else {
        echo "<option value=\"$value\">$value</option>";
    }
}
echo "</select>";

mysql_close($con);

?>
</div>
</div>
<div class="form-actions">
<input type="hidden" id="idtoedit" name="idtoedit" value="<?php echo $idtoedit; ?>" />
<button type="submit" class="btn btn-primary">Update</button>
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
        format: "dd/mm/yyyy",
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
