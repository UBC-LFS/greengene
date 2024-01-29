<?php
require_once('../includes/global.php');

// DATABASE CONNECTION
$g_db = new DB();

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
// $user = (new Security) -> getUser();
$user = (new Security)->getUser(); // php8


// - get form variables
$formaction = false;
if(isset($_POST['formaction'])) {
	$formaction = $_POST['formaction'];
}

$courseId = false;
if(isset($_GET['courseId'])) {
	$courseId = $_GET['courseId'];
}

// PAGE CREATION LOGIC
if(empty($courseId))
	$page = new Page($user, 'Create Course', 10);
else
	$page = new Page($user, 'Manage Course', 10);


// FORM LOGIC
if($formaction == 'addcourse')
{
	// check form values
	$formOk = true;

	if($_POST['CourseName'] == '')
	{
		(new UserError) -> addError(908);
		$formOk = false;
	}

	if($_POST['UserId'] == '')
	{
		(new UserError) -> addError(302);
		$formOk = false;
	}

	if($formOk == true)
	{
		$newCourseId = $user->createCourse($_POST['CourseName'],
			$_POST['CourseDescription'],
			$_POST['UserId'],
			$_POST['FirstName'],
			$_POST['LastName']);
		if($newCourseId != false)
			// Page::redirect("managecourse.php?courseId=$newCourseId");
			$page -> redirect("managecourse.php?courseId=$newCourseId");
	}
}
elseif($formaction == 'savecourse')
{
	$courseId = $_POST['CourseId'];

	$formOk = true;

	// check form values
	if($_POST['CourseName'] == '')
	{
		(new UserError) -> addError(908);
		$formOk = false;
	}

	if($formOk == true && $user->modifyCourse($courseId, $_POST['CourseName'], $_POST['CourseDescription']) == true)
	{
		// Page::redirect("viewcourses.php");
		$page -> redirect("viewcourses.php");
	}
}
elseif($formaction == 'deleteuser')
{
	$courseId = $_POST['CourseId'];
	// $delUser = $_POST['delUser'];
	$delUser = isset($_POST['delUser'])? $_POST['delUser'] : null;

	if(isset($delUser) && count($delUser) > 0)
		foreach($delUser as $userId)
			$user->deleteManagementUser($userId, $courseId);
}

// write page header, including toolbar
$page->writeHeader();

// DATA LOGIC
if(!empty($courseId))
{
	$course = $user->getCourse($courseId);
	$users = $user->getManagementUsers($courseId);
}

// handle errors
$page->handleErrors();

// page content
if(!empty($courseId))
{
	// COURSE SETTINGS

	echo("<form action=\"".htmlentities($_SERVER['PHP_SELF'])."\" method=\"post\">");
	echo("<input type=\"hidden\" name=\"formaction\" value=\"savecourse\">");
	echo("<input type=\"hidden\" name=\"CourseId\" value=\"$courseId\">");

	$table = new Table(2, false, true);
	$row = $g_db->fetch($course);

	$table->writeRow('Course Name:',
		"<input type=\"text\" name=\"CourseName\" value=\"$row->Name\" maxlength=\"30\" size=\"20\">");
	$table->writeRow('Course Description:',
		"<input type=\"text\" name=\"CourseDescription\" value=\"$row->Description\" maxlength=\"250\" size=\"40\">");
	$table->flush();

	echo("<p><input type=\"submit\" value=\"Save\">");
	echo("</form>");


	// COURSE ADMINS
	$page->writeSectionHeader('Course Administrators');

	echo("<form action=\"".htmlentities($_SERVER['PHP_SELF'])."\" method=\"post\">");
	echo("<input type=\"hidden\" name=\"formaction\" value=\"deleteuser\">");
	echo("<input type=\"hidden\" name=\"CourseId\" value=\"$courseId\">");

	$table = new Table(6, true, true);
	$table->writeHeaders('', 'CWL Username', 'First Name', 'Last Name', 'User Type', '');

	$priv = array(1 => 'Professor', 2 => 'TA');

	while($row = $g_db->fetch($users))
	{

		// Ensures user's privilege level is for this course 
		$courseIdArray = explode(',', $row->CourseId);

		$privilegeLevelArray = explode(',', $row->PrivilegeLvl);

		// User courseID = courseID we are looking for
		$indexOfCourse = array_search($user->m_courseId, $courseIdArray);

		$table->writeRow("<input type=\"checkbox\" name=\"delUser[]\" value=\"$row->UserId\">",
			$row->UserId,
			$row->FirstName,
			$row->LastName,
			$priv[$privilegeLevelArray[$indexOfCourse]],
			"<input type=\"button\" value=\"Modify\" onClick=\"goUrl('modifyadmin.php?userId=$row->UserId&courseId=$courseId');\">");
	}
	$table->flush();

	echo("<p><input type=\"submit\" value=\"Delete Selected\"> &nbsp;&nbsp;
	<input type=\"button\" onClick=\"goUrl('modifyadmin.php?courseId=$courseId')\" value=\"Add Admin\"></p>");
	echo("</form>");
}
else
{
	
	if(!isset($_POST['CourseName']) || !isset($_POST['CourseDescription']) || !isset($_POST['UserId']) 
		|| !isset($_POST['FirstName']) || !isset($_POST['LastName'])) {
			$_POST['CourseName'] = false;
			$_POST['CourseDescription'] = false;
			$_POST['UserId'] = false;
			$_POST['FirstName'] = false;
			$_POST['LastName'] = false;
		}
		
	// COURSE SETTINGS
	echo("<form action=\"".htmlentities($_SERVER['PHP_SELF'])."\" method=\"post\">");
	echo("<input type=\"hidden\" name=\"formaction\" value=\"addcourse\">");

	$table = new Table(2, false, true);

	$table->writeRow('Course Name:',
		"<input type=\"text\" name=\"CourseName\" value=\"" .$_POST['CourseName']. "\" maxlength=\"30\" size=\"20\">");
	$table->writeRow('Course Description:',
		"<input type=\"text\" name=\"CourseDescription\" value=\"" . $_POST['CourseDescription'] . "\" maxlength=\"250\" size=\"40\">");

	$table->writeDivider();

	$table->writeRow('Admin CWL Username:',
		"<input type=\"text\" name=\"UserId\" value=\"" . $_POST['UserId'] . "\" maxlength=\"10\">");

	$table->writeRow('First Name:',
		"<input type=\"text\" name=\"FirstName\" value=\"" . $_POST['FirstName'] . "\" maxlength=\"20\" size=\"20\">");
	$table->writeRow('Last Name:',
		"<input type=\"text\" name=\"LastName\" value=\"" .$_POST['LastName'] . "\" maxlength=\"20\" size=\"20\">");

	$table->flush();

	echo("<p><input type=\"submit\" value=\"Add\"></p>");
	echo("</form>");
}

// write main footer and close database connection
$page->writeFooter();
$g_db->disconnect();
?>
