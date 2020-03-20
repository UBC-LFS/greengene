<?php
require_once('../includes/global.php');

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
$user = Security::getUser();

// PAGE CREATION LOGIC
$page = new Page($user, 'Modify Student', 2);

// DATABASE CONNECTION
$g_db = new DB();

// FORM LOGIC
// - get form variables
$studentId = $_GET['studentId'];
$formaction = false;
if (isset($_POST['formaction'])) {
	$formaction = $_POST['formaction'];
}

$showStudentForm = false;
$formError = false;
$studentRecordset;
$studentProblem;

// variables for form logic
$inputUserId;
$inputFirstName;
$inputLastName;
$inputProgenyPerMating;
$inputMaxProgeny;

// =============================================================================
// Logic for handling form
// =============================================================================
if ($formaction == "modifystudent")
{
	$inputUserId = $_POST['userId'];
	$inputFirstName = $_POST['firstName'];
	$inputLastName = $_POST['lastName'];
	$inputProgenyPerMating = -1;
	$inputMaxProgeny = -1;

	if (empty($inputFirstName) || empty($inputLastName) )
	{
		UserError::addError(650);
		$formError = true;
	}

	if (!empty($_POST['progenychange']))
	{
		$inputProgenyPerMating = $_POST['progenyPerMating'];
		$inputMaxProgeny = $_POST['maxProgeny'];

		if (empty($inputProgenyPerMating) || $inputProgenyPerMating < 0)
		{
			UserError::addError(751);
			$formError = true;
		}
		if (empty($inputMaxProgeny) || $inputMaxProgeny < 0)
		{
			UserError::addError(752);
			$formError = true;
		}
		
		// check to make sure that the new progenypermating and maxprogeny
		// are actually new values
		$studentProblem = $user->getStudentProblem($inputUserId);
		if (!empty($studentProblem))
		{			
			$row = $g_db->fetch($studentProblem);
			if ($row->ProgenyPerMating == $inputProgenyPerMating &&
				$row->MaxProgeny == $inputMaxProgeny)
			{
				$inputProgenyPerMating = -1;
				$inputMaxProgeny = -1;				
				// set to default values so we don't have to update it in
				// the DB
			}
		}
				
	}

	if ($formError != true)
	{
		$user->modifyStudent($inputUserId, $inputFirstName, $inputLastName,
						   $inputProgenyPerMating,$inputMaxProgeny);

		if (count($_POST['reset_password']) && !UserError::hasError())
		{
			$user->resetPassword($inputUserId);
		}

		if (!UserError::hasError())
		{			
			$page->redirect("viewstudentlist.php");
		}
	}
}
else
{
	$studentRecordset = $user->getStudent($studentId);
	if (empty($studentRecordset) || $g_db->getNumRows($studentRecordset) == 0)
	{
		UserError::addError(603);
	}
	else
	{
		// retrieve the student problem too
		$studentProblem = $user->getStudentProblem($studentId);
		$showStudentForm = true;
	}
}

// write page header, including toolbar
$page->writeHeader();
$page->handleErrors();

// LOGIC FOR SHOWING PROBLEM FORM

if ($showStudentForm == true)
{
	$row = $g_db->fetch($studentRecordset);
	echo "<form name=\"createstudent\" action=\"".htmlentities($_SERVER['PHP_SELF'])."\" method=\"post\">";
	echo("<input type=\"hidden\" name=\"formaction\" value=\"modifystudent\">");
	echo("<input type=\"hidden\" name=\"userId\" value=\"" . $row->UserId . "\">");

	$studentTable = new Table(2, false, true);
	$studentTable->writeRow("UserId:",$row->UserId);
	$studentTable->writeRow("First Name:",
		"<input type=\"text\" name=\"firstName\" maxlength=\"20\" value=\"" . $row->FirstName . "\">");
	$studentTable->writeRow("Last Name:",
		"<input type=\"text\" name=\"lastName\" maxlength=\"20\" value=\"" . $row->LastName . "\">");

	if (!empty($studentProblem) && $g_db->getNumRows($studentProblem) > 0)
	{
		$problemRow = $g_db->fetch($studentProblem);
		echo("<input type=\"hidden\" name=\"progenychange\" value=\"yes\">");

		$studentTable->writeRow("Progeny Per Mating:",
			"<input type=\"text\" name=\"progenyPerMating\" maxlength=\"5\" size=\"5\" value=\"" . $problemRow->ProgenyPerMating . "\">");
		$studentTable->writeRow("Max Progeny:",
			"<input type=\"text\" name=\"maxProgeny\" maxlength=\"5\" size=\"5\" value=\"" . $problemRow->MaxProgeny . "\">");
	}
	else
	{
		$studentTable->writeRow("Progeny Per Mating:",
			"<input type=\"text\" name=\"progenyPerMating\" maxlength=\"5\" size=\"5\" disabled=\"true\"> (Requires a problem to be assigned first.)");
		$studentTable->writeRow("Max Progeny:",
			"<input type=\"text\" name=\"maxProgeny\" maxlength=\"5\" size=\"5\" disabled=\"true\"> (Requires a problem to be assigned first.)");

	}
	$studentTable->flush();

	echo "<p><input type=\"submit\" name=\"submit\" value=\"Save\"></p>";
	echo "</form>";
}

$page->writeFooter();
$g_db->disconnect();
?>
