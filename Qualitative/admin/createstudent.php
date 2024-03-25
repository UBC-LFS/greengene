<?php
require_once('../includes/global.php');

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
$user = (new Security) -> getUser();


// PAGE CREATION LOGIC
$page = new Page($user, 'Create Student', 2);

// DATABASE CONNECTION
$g_db = new DB();
$userId = $user->m_userId;

// FORM LOGIC
// - get form variables
$formaction = false;
if(isset($_POST['formaction'])) {
	$formaction = $_POST['formaction'];
}

$showStudentForm = false;

// variables for form logic
$inputUserId;
$inputFirstName;
$inputLastName;

// =============================================================================
// Logic for handling form
// =============================================================================


$inputUserId = false;
$inputFirstName = false;
$inputLastName = false;

if (isset($_POST['userId'])) 
	$inputUserId = $_POST['userId'];
if (isset($_POST['firstName']))	
	$inputFirstName = $_POST['firstName'];
if (isset($_POST['lastName']))
	$inputLastName = $_POST['lastName'];

if ($formaction == "createstudent")
{

	if (empty($inputUserId) )
	{
		(new UserError) -> addError(650);
	}
	else
	{
		if ($user->createStudent($inputUserId, $inputFirstName, $inputLastName) == true)
		{	
			// if (count($_POST['assign_problem']))
			if (is_countable($_POST['assign_problem']) && count($_POST['assign_problem']))
			{
				$page->redirect("selectproblem.php?studentId=".$inputUserId);
			}
			else
			{
				$page->redirect("viewstudentlist.php");
			}
		}
	}
}

// write page header, including toolbar
$page->writeHeader();
$page->handleErrors();


// LOGIC FOR SHOWING PROBLEM FORM

echo "<form name=\"createstudent\" action=\"".htmlentities($_SERVER['PHP_SELF'])."\" method=\"post\">";
echo("<input type=\"hidden\" name=\"formaction\" value=\"createstudent\">");

$studentTable = new Table(2, false, true);
$studentTable->writeRow("CWL Username:","<input type=\"text\" name=\"userId\" maxlength=\"10\" value=\"$inputUserId\">");
$studentTable->writeRow("First Name:","<input type=\"text\" name=\"firstName\" maxlength=\"20\" value=\"$inputFirstName\">");
$studentTable->writeRow("Last Name:","<input type=\"text\" name=\"lastName\" maxlength=\"20\" value=\"$inputLastName\">");
$studentTable->writeRow("Assign Problem Now:", "<input type=\"checkbox\" name=\"assign_problem[]\" value=\"yes\">");
$studentTable->writeSpanningRow("<input type=\"submit\" name=\"submit\" value=\"Create Student\">");
$studentTable->flush();

echo "</form>";

$page->writeFooter();
$g_db->disconnect();
?>
