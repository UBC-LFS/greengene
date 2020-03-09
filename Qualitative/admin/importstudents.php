<?php
require_once('../includes/global.php');

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
$user = Security::getUser();

// PAGE CREATION LOGIC
$page = new Page($user, 'Import Students', 2);

// DATABASE CONNECTION
$g_db = new DB();
$userId = $user->m_userId;


//$ta = new TA($userId);
$studentListArray;
$studentErrorListArray;
$showStudentRecords = false;
$showBrowseBox = true;

$numStudentRecords;
$numErrorRecords;

// FORM LOGIC
// - get form variables
$formaction = $_POST['formaction'];

if ($formaction == "createstudents")
{
	$createStudent = $_POST['create_student'];
	$showBrowseBox = false;
	$createStudentError = false;
	//echo "<p>Students created: ";
	for( $i = 0; $i < count($createStudent); $i++)
	{
		$pos = $createStudent[$i];
		$inputUserId = $_POST['userId'.$pos];
		$inputFirstName = $_POST['firstName'.$pos];
		$inputLastName = $_POST['lastName'.$pos];
		if ($user->createStudent($inputUserId,$inputFirstName,$inputLastName)!=true)
		{
			$createStudentError = true;
		}
		else
		{
			// now, we try to assign the problem to a student
			$inputProblemId = $_POST['problem'.$pos];
			if (!empty($inputProblemId) && $inputProblemId != -1)
			{
				if ($user->assignProblem($inputUserId,$inputProblemId)!=true)
				{
					$createStudentError = true;
				}
			}
		}
	}

	if ($createStudentError == false)
	{
		$page->redirect("viewstudentlist.php");
	}
}
else if ($formaction == "loadfile")
{
	$user->importStudents(uploaded(),&$studentListArray,&$studentErrorListArray);
	$numStudentRecords = count($studentListArray);
	$numErrorRecords = count($studentErrorListArray);
	$showStudentRecords = true;
	$showBrowseBox = false;
}


// only necessary on Student pages
// $student = $page->translateUser($userId);
// note: Student is now an appropriate object of either the current student
//       or a student object representing the admin viewing the student's data


// write page header, including toolbar
$page->writeHeader();

// DATA LOGIC

function uploaded()
{
	if (is_uploaded_file($_FILES['theFile']['tmp_name']))
	{
	    $lines = file($_FILES['theFile']['tmp_name']);
	    //$all = implode("\n", $lines);
	    return $lines;
	}
	else
	{
		UserError::addError(607);//upload problem.
	}
}

function loadProblemsFromRecordset($p_recordset,$p_problemIdArray, $p_problemNameArray)
{
	global $g_db;
	$counter = $g_db->getNumRows($p_recordset);

	$currRow;

	// iterate through each row, and get the information
	for ($i = 0; $i < $counter; $i++)
	{
		$currRow = $g_db->fetch($p_recordset);
		$p_problemIdArray[$i]= $currRow->ProblemId;
		$p_problemNameArray[$i] = $currRow->Name;
	}
}

function generateProblemSelectBox($p_name, $p_problemIdArray, $p_problemNameArray)
{
	$counter = count($p_problemIdArray);

	$selectBox = "<select name=\"". $p_name . "\">\n";

	$selectBox = $selectBox. "<option value=\"-1\">None";
	// iterate through each row, and get the information
	for ($i = 0; $i < $counter; $i++)
	{
		$option = "<option value=\"" . $p_problemIdArray[$i] . "\">" . $p_problemNameArray[$i]."\n";
		$selectBox = $selectBox.$option;
	}

	$selectBox = $selectBox."</select>\n";
	return $selectBox;
}


if ($showBrowseBox == true )
{
	echo "<p>Please browse for a CSV file that contain entries for User Id,
		  First Name, and Last Name on each line (with each entry separated by commas).<p>";
	echo "<form action=\"".$PHP_SELF."\" method=\"post\" enctype=\"multipart/form-data\">";
	echo("<input type=\"hidden\" name=\"formaction\" value=\"loadfile\">");
	//echo "<font size=-1 color=\"#E24A00\">File Upload Script</font><br>";
	echo "<input type=\"file\" name=\"theFile\" size=\"50\" class=\"formtextfield\"><br>";
	echo "<input type=\"submit\" value=\"Load\">";
	echo "</form>";

}
else if ($showStudentRecords == true)
{
	if ($numStudentRecords > 0)
	{
		$problemIdArray;
		$problemNameArray;
		$problemRecordset = $user->getProblems();
		if (!empty($problemRecordset) )
		{
			loadProblemsFromRecordset($problemRecordset,&$problemIdArray,&$problemNameArray);
		}

		// now generate the content of the page

		echo "<p>" . $numStudentRecords . " student record(s) were parsed successfully.</p>";
		echo "<p>" . $numErrorRecords . " student record(s) were parsed unsuccessfully.</p>";

		// Start the form
		echo "<form action=\"". $PHP_SELF . "\" method=\"post\">";
		echo("<input type=\"hidden\" name=\"formaction\" value=\"createstudents\">");

		$studentTable = new Table(5);

		$studentTable->writeHeaders("Create","UserID", "First Name", "Last Name", "Assign Problem");


		// first, show the lists of records that did not have any errors
		for ($i = 0; $i < $numStudentRecords; $i++)
		{
			$record = $studentListArray[$i];
			$userIdBox = "<input type=\"text\" name=\"userId".$i."\" value=\"" . $record[0] . "\" size=\"10\">";
			$firstNameBox = "<input type=\"text\" name=\"firstName".$i."\" value=\"" . $record[1] . "\" size=\"20\">";
			$lastNameBox = "<input type=\"text\" name=\"lastName".$i."\" value=\"" . $record[2] . "\" size=\"20\">";
			$checkBox	 = "<input type=\"checkbox\" name=\"create_student[]\" value=\"" . $i . "\" CHECKED>";
			$problemSelectBox = generateProblemSelectBox("problem".$i,$problemIdArray,$problemNameArray);
			$studentTable->writeRow(&$checkBox,&$userIdBox,&$firstNameBox,&$lastNameBox,&$problemSelectBox);
		}


		// now, show the list of records with errors

		for ($i = $numStudentRecords; $i < $numErrorRecords + $numStudentRecords; $i++)
		{
			$record = $studentErrorListArray[$i - $numStudentRecords];
			$userIdBox = "<input type=\"text\" name=\"userId".$i."\" value=\"" . $record[0] . "\" size=\"10\">";
			$firstNameBox = "<input type=\"text\" name=\"firstName".$i."\" value=\"" . $record[1] . "\" size=\"20\">";
			$lastNameBox = "<input type=\"text\" name=\"lastName".$i."\" value=\"" . $record[2] . "\" size=\"20\">";
			$checkBox	 = "<input type=\"checkbox\" name=\"create_student[]\" value=\"" . $i . "\">";
			$problemSelectBox = generateProblemSelectBox("problem".$i,$problemIdArray,$problemNameArray);
			$studentTable->writeRow(&$checkBox,&$userIdBox,&$firstNameBox,&$lastNameBox,&$problemSelectBox);
		}

		$studentTable->flush();

		// the delete button
		echo "<p><input type=\"submit\" value=\"Create Students\"></p>";

		// end the form
		echo "</form>";
	}
	else
	{
		UserError::addError(652);
	}
}
$page->handleErrors();
$page->writeFooter();
$g_db->disconnect();
?>
