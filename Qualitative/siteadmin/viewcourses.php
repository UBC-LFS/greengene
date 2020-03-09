<?php
require_once('../includes/global.php');

// DATABASE CONNECTION
$g_db = new DB();

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
$user = Security::getUser();

// PAGE CREATION LOGIC
$page = new Page($user, 'Manage Courses', 10);


// FORM LOGIC
// - get form variables
$formaction = $_POST['formaction'];
if($formaction == 'delete')
{
	$delCourse = $_POST['delCourse'];
	$password = $_POST['password'];

	if(isset($delCourse) && count($delCourse) > 0)
		foreach($delCourse as $delCourseId)
			$user->deleteCourse($delCourseId, $password);
}

// write page header, including toolbar
$page->writeHeader();

// DATA LOGIC
$courses = $user->getCourses();

// handle errors
$page->handleErrors();

// page content


$table = new Table(4, true, true);
$table->writeHeaders('', 'Course Name', 'Course Description', '');

echo("<form action=\"$PHP_SELF\" method=\"post\">");
echo("<input type=\"hidden\" name=\"formaction\" value=\"delete\">");

while($row = $g_db->fetch($courses))
	$table->writeRow("<input type=\"checkbox\" name=\"delCourse[]\" value=\"$row->CourseId\">",
		$row->Name,
		$row->Description,
		"<input type=\"button\" value=\"Modify\" onClick=\"goUrl('managecourse.php?courseId=$row->CourseId');\">");

$table->flush();

echo("<p>Enter password to confirm deletion:<br>");
echo("<input type=\"password\" name=\"password\"></p>");

echo("<p><input type=\"submit\" value=\"Delete Selected\">");
echo("</form>");

// write main footer and close database connection
$page->writeFooter();
$g_db->disconnect();
?>
