<?php
require_once('../includes/global.php');

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
$user = Security::getUser();

// PAGE CREATION LOGIC
$page = new Page($user, 'View Student', 2);

// DATABASE CONNECTION
$g_db = new DB();

// FORM LOGIC
// - get form variables
$studentId = $_GET['studentId'];

$studentRecordset = $user->getStudent($studentId);
$studentProblem = $user->getStudentProblem($studentId);

// write page header, including toolbar
$page->writeHeader();
$page->handleErrors();

if (empty($studentRecordset))
{
	UserError::addError(654);
}
else
{
	$row = $g_db->fetch($studentRecordset);
	$studentTable = new Table(2, false, true);
	$studentTable->writeRow("User Id:", $row->UserId);
	$studentTable->writeRow("First Name:", $row->FirstName);
	$studentTable->writeRow("Last Name:", $row->LastName);
	$studentTable->flush();

	echo("<p>Problem Assigned: ");
	if (!empty($studentProblem) && $g_db->getNumRows($studentProblem) > 0)
	{
		$problemRow = $g_db->fetch($studentProblem);
		echo "<a href=\"viewproblem.php?studentId=" . $row->UserId . "\">" .
			 $problemRow->Name . "</a>";
	}
	else
	{
		echo " none.";
	}
	echo "</p>\n";
}

$page->writeFooter();
$g_db->disconnect();
?>
