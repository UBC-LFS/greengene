<?php
require_once('../includes/global.php');

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
$user = Security::getUser();

// DATABASE CONNECTION
$g_db = new DB();

// FORM LOGIC
// - get form variables
$studentId = $_GET['studentId']? $_GET['studentId']:$_POST['studentId'];

// PAGE CREATION LOGIC
$page = new Page($user, 'Assign Problem', 2);

//if assign a problem without modification
//if($_GET['problemId'])
if(isset($_GET['problemId']))
{
	$rs = $user->getStudentProblem($studentId);
	if( !empty($rs) && $g_db->getNumRows($rs)<= 0 )
	{
		if( !$user->assignProblem($studentId,$_GET['problemId']) )
		{
			UserError::addError(763);
		}
		else
		{
			//$page->redirect("viewstudent.php?studentId=".$studentId);
			$page->redirect("viewstudentlist.php");
		}
	}
	else
	{
		if( !$user->reassignProblem($studentId,$_GET['problemId']))
		{
			UserError::addError(763);
		}
		else
		{
			//$page->redirect("viewstudent.php?studentId=".$studentId);
			$page->redirect("viewstudentlist.php");
		}
	}
}

// retrieve the list of problems associated with the user's courseId
$recordset = $user->getProblems();
$student = new Student($studentId);

// write page header, including toolbar
$page->writeHeader();
$page->handleErrors();

echo("<p>Assigning Problem for Student: $student->m_firstName $student->m_lastName ($studentId)</p>");
//echo("<p>Assigning Problem for Student: $student->m_firstName $student->lastName ($studentId)</p>");

$problemTable = new Table(4, true, false);
$problemTable->writeHeaders('Name', 'Descriptions', 'Last Modified', '');

while($row = $g_db->fetch($recordset))
{
	$problemDesc = $row->Description;
	if(strlen($problemDesc) > 25)
		$problemDesc = substr($problemDesc, 0, 22) . '...';

	$problemDate = date("m-d-Y G:i", $row->FormattedTime);

	if ($user->m_privilegeLvl <= 1)
	{
		$problemTable->writeRow($row->Name,
			$problemDesc,
			$problemDate,
			"<input type=\"button\" value=\"View\" onClick=\"goUrl('viewproblem.php?problemId=$row->ProblemId');\">
			<input type=\"button\" value=\"Assign\" onClick=\"goUrl('selectproblem.php?problemId=$row->ProblemId&studentId=$studentId ');\">
			<input type=\"button\" value=\"Modify and Assign\" onClick=\"goUrl('modifyproblem.php?problemId=$row->ProblemId&studentId=$studentId');\">");
	}
	else
	{
		$problemTable->writeRow($row->Name,
			$problemDesc,
			$problemDate,
			"<input type=\"button\" value=\"View\" onClick=\"goUrl('viewproblem.php?problemId=$row->ProblemId');\">
			<input type=\"button\" value=\"Assign\" onClick=\"goUrl('selectproblem.php?problemId=$row->ProblemId&studentId=$studentId ');\">");	
	}
}
$problemTable->flush();

// end the form
echo("</form>");

$page->writeFooter();
$g_db->disconnect();
?>
