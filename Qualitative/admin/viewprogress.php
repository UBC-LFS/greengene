<?php
require_once('../includes/global.php');

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
$user = (new Security) -> getUser();

// DATABASE CONNECTION
$g_db = new DB();

// PAGE CREATION LOGIC
$page = new Page($user, 'View Student Progress', 2);

// write page header, including toolbar
$page->writeHeader();

// retrieve the list of problems associated with the user's courseId
$recordset = $user->getStudents();

$page->handleErrors();

// Start the table
$studentTable = new Table(5, true, false);

$studentTable->writeHeaders('CWL Username', 'First Name', 'Last Name', 'Assigned Problem', '');

// iterate through each row, and get the information
while($row = $g_db->fetch($recordset))
{
	if(empty($row->Name))
		$problem = 'Not Assigned';
	else
		$problem = "<a href=\"viewproblem.php?studentId=$row->UserId\">$row->Name</a>";

	$studentTable->writeRow($row->UserId,
		$row->FirstName,
		$row->LastName,
		$problem,
		"<input type=\"button\" value=\"View Progress\" onClick=\"goUrl('" . URLROOT .
		"/student/viewprogeny.php?_userId=$row->UserId');\">");
}

$studentTable->flush();

// write main footer and close database connection
$page->writeFooter();
$g_db->disconnect();
//no need to worry about this part at this point
?>
