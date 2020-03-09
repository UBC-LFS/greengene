<?php
require_once('../includes/global.php');

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
$user = Security::getUser();

// DATABASE CONNECTION
$g_db = new DB();

// PAGE CREATION LOGIC
$page = new Page($user, 'Manage Students', 2);

// write page header, including toolbar
$page->writeHeader();

// check for previous page's deleted problems
$delStudent = $_POST['del_student'];
if(count($delStudent) > 0)
{
	for( $i = 0; $i < count($delStudent); $i++)
		$user->deleteStudent($delStudent[$i]);
}

// retrieve the list of problems associated with the user's courseId
$recordset = $user->getStudents();

$page->handleErrors();

// Start the form
echo "<form action=\"$PHP_SELF\" method=\"post\">";

$studentTable = new Table(6, true, true);

$studentTable->writeHeaders('', 'User Id', 'First Name', 'Last Name', 'Assigned Problem', '');

// iterate through each row, and get the information
while($row = $g_db->fetch($recordset))
{
	if(empty($row->Name))
		$problem = 'Not Assigned';
	else
		$problem = "<a href=\"viewproblem.php?studentId=$row->UserId\">$row->Name</a>";

	$studentTable->writeRow("<input type=\"checkbox\" name=\"del_student[]\" value=\"$row->UserId\">",
		$row->UserId,
		$row->FirstName,
		$row->LastName,
		$problem,
		"<input type=\"button\" value=\"Modify\" onClick=\"goUrl('modifystudent.php?studentId=$row->UserId');\">
		<input type=\"button\" value=\"Assign Problem\" onClick=\"goUrl('selectproblem.php?studentId=$row->UserId');\">");
}
$studentTable->flush();

// end the form
echo("<p><input type=\"submit\" value=\"Delete Selected\"> &nbsp;&nbsp;
<input type=\"button\" value=\"Create Student\" onClick=\"goUrl('createstudent.php');\">
<input type=\"button\" value=\"Import Students\" onClick=\"goUrl('importstudents.php');\"></p>");
echo "</form>";

// write main footer and close database connection
$page->writeFooter();
$g_db->disconnect();
?>
