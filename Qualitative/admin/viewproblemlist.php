<?php
require_once('../includes/global.php');

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object

// $user = (new Security) -> getUser();

// DATABASE CONNECTION
$g_db = new DB();

$user = (new Security)->getUserClass($_GET['course']); // php8

// PAGE CREATION LOGIC
$page = new Page($user, 'Manage Problem Templates', 1);

// write page header, including toolbar
$page->writeHeader();

if(isset($_POST['del_prob'])) {
	$delProblem = $_POST['del_prob'];
	if(count($delProblem) > 0)
		for( $i = 0; $i < count($delProblem); $i++)
			$user->deleteProblem($delProblem[$i]);
}

// retrieve the list of problems associated with the user's courseId
$recordset = $user->getProblems();

$page->handleErrors();

// Start the form
echo("<form action=\"viewproblemlist.php\" method=\"post\">");

$problemTable = new Table(5, true, true);
$problemTable->writeHeaders('', 'Name', 'Descriptions', 'Last Modified', '');

while($row = $g_db->fetch($recordset))
{
	$problemDesc = $row->Description;
	if(strlen($problemDesc) > 25)
		$problemDesc = substr($problemDesc, 0, 22) . '...';

	$problemDate = date("m-d-Y G:i", $row->FormattedTime);

	$problemTable->writeRow("<input type=\"checkbox\" name=\"del_prob[]\" value=\"". $row->ProblemId. "\">",
		$row->Name,
		$problemDesc,
		$problemDate,
		"<input type=\"button\" value=\"View\" onClick=\"goUrl('viewproblem.php?problemId=$row->ProblemId');\">
		<input type=\"button\" value=\"Modify\" onClick=\"goUrl('modifyproblem.php?problemId=$row->ProblemId');\">
		<input type=\"button\" value=\"New Problem\" onClick=\"goUrl('createproblem.php?problemId=$row->ProblemId');\">");
}
$problemTable->flush();

// the delete button
echo("<p><input type=\"submit\" value=\"Delete Selected\"> &nbsp;&nbsp;
<input type=\"button\" value=\"Create New Problem\" onClick=\"goUrl('createproblem.php');\">");

// end the form
echo("</form>");

$page->writeFooter();
$g_db->disconnect();
?>
