<?php
require_once('../includes/global.php');

// DATABASE CONNECTION
$g_db = new DB();

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
// $user = (new Security) -> getUser();
$user = (new Security)->getUser();

// PAGE CREATION LOGIC
$page = new Page($user, 'Manage Courses', 10);


// FORM LOGIC
// - get form variables
$formaction = false;
if(isset($_POST['formaction'])) {
	$formaction = $_POST['formaction'];
}
if($formaction == 'delete')
{
	$delCourse = isset($_POST['delCourse']) ? $_POST['delCourse']: null;

	if(isset($delCourse) && count($delCourse) > 0)
		foreach($delCourse as $delCourseId)
			$user->deleteCourse($delCourseId);
}

// write page header, including toolbar
$page->writeHeader();

// DATA LOGIC
$courses = $user->getCourses();

// handle errors
$page->handleErrors();

// page content


$table = new Table(4, true, true);
$table->writeHeaders('','Course Name', 'Course Description', '');

echo("<form action=\"".htmlentities($_SERVER['PHP_SELF'])."\" method=\"post\">");
echo("<input type=\"hidden\" name=\"formaction\" value=\"delete\">");

while($row = $g_db->fetch($courses))
	$table->writeRow("",
		$row->Name,
		$row->Description,
		"<input type=\"button\" value=\"Modify\" onClick=\"goUrl('managecourse.php?courseId=$row->CourseId');\">
		<input type=\"button\" value=\"Delete Selected\" onClick=\"goUrl('deletecourse.php?courseId=$row->CourseId');\">");

$table->flush();

echo("</form>");

// write main footer and close database connection
$page->writeFooter();
$g_db->disconnect();
?>
