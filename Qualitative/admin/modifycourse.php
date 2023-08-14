<?php
require_once('../includes/global.php');

// DATABASE CONNECTION
$g_db = new DB();

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
$user = (new Security) -> getUser();


// PAGE CREATION LOGIC
$page = new Page($user, 'Modify Course', 1);


// FORM LOGIC
// - get form variables
$formaction = false;
if(isset($_POST['formaction'])) {
	$formaction = $_POST['formaction'];
}

if($formaction == 'save')
{
	$courseName = $_POST['CourseName'];
	$courseDesc = $_POST['CourseDescription'];

	if($user->modifyCourse($courseName, $courseDesc) == true)
	{
		$user->m_courseName = $courseName;
		$user->m_courseDescription = $courseDesc;
		$_SESSION['userSession'] = $user;
		Page::redirect($_SERVER['PHP_SELF']);
	}
}
else
{
	$courseName = $user->m_courseName;
	$courseDesc = $user->m_courseDescription;
}

// write page header, including toolbar
$page->writeHeader();

// handle errors
$page->handleErrors();

// page content
echo("<form action=\"".htmlentities($_SERVER['PHP_SELF'])."\" method=\"post\">");
echo("<input type=\"hidden\" name=\"formaction\" value=\"save\">");

$table = new Table(2, false, true);

$table->writeRow('Course Name:',
	"<input type=\"text\" name=\"CourseName\" value=\"$courseName\" maxlength=\"30\">");

$table->writeRow('Course Description:',
	"<input type=\"text\" name=\"CourseDescription\" value=\"$courseDesc\" size=\"30\" maxlength=\"250\">");

$table->writeSpanningRow('<input type="submit" value="Save">');
$table->flush();

echo('</form>');

// write main footer and close database connection
$page->writeFooter();
$g_db->disconnect();
?>
