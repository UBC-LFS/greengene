<?php
require_once('../includes/global.php');

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
// $user = (new Security) -> getUser();
// $user = (new Security) -> getUser();

// // DATABASE CONNECTION
// $g_db = new DB();

$g_db = new DB();

$user = (new Security)->getUserClass($_GET['course']); // php8


// PAGE CREATION LOGIC
$page = new Page($user, 'Manage Students', 2);

// write page header, including toolbar
$page->writeHeader();

// check for previous page's deleted problems
if(isset($_POST['del_student'])) {
	$delStudent = $_POST['del_student'];
	if(count($delStudent) > 0)
	{
		for( $i = 0; $i < count($delStudent); $i++)
			$user->deleteStudent($delStudent[$i]);
	}
}

if(isset($_POST['deleteAll'])) {
	$user->deleteAllStudents();
}


// var_dump("Did adding courseid and courseid as a key break anything?");

// retrieve the list of problems associated with the user's courseId
$recordset = $user->getStudents();


$page->handleErrors();

// Start the form
echo "<form action=\"".htmlentities($_SERVER['PHP_SELF'])."\" method=\"post\">";

$studentTable = new Table(6, true, true);

$studentTable->writeHeaders('', 'CWL Username', 'First Name', 'Last Name', 'Assigned Problem', '');

// var_dump($row = $g_db->fetch($recordset));

// var_dump("Bug, row->courseId is NULL, why are student course ids not showing up?");

// iterate through each row, and get the information

// var_dump($recordset);

while($row = $g_db->fetch($recordset))
{
	// var_dump($row);
	// Ensures user's privilege level is for this course 
	$courseIdArray = explode(',', $row->CourseId);

	$privilegeLevelArray = explode(',', $row->PrivilegeLvl);

	// User courseID = courseID we are looking for
	$indexOfCourse = array_search($user->m_courseId, $courseIdArray);
	
	// echo '<pre>';

	// 	var_dump($row);
	// 	var_dump($row->CourseId);
	// 	var_dump($courseIdArray);
	// 	var_dump($user->m_courseId);
	// 	var_dump($indexOfCourse);
	// echo '</pre>';

	if ($privilegeLevelArray[$indexOfCourse] == 3) {
		// var_dump("Testing to see if correct problem displays");
		// var_dump($row->Name);

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
}
$studentTable->flush();

// end the form
echo("<p><input type=\"submit\" value=\"Delete Selected\"> &nbsp;&nbsp;
	<input type=\"button\" value=\"Create Student\" onClick=\"goUrl('createstudent.php');\">
	<input type=\"button\" value=\"Import Students\" onClick=\"goUrl('importstudents.php');\">
	</p>");
echo "</form>";


echo "<form style=\"margin: 0; padding: 0;\" action=\"".htmlentities($_SERVER['PHP_SELF'])."\" method=\"post\">";
echo "<input style=\"display:inline;\" type=\"hidden\" name=\"deleteAll\" value=\"deleteAll\"> </input>"; 
echo "<input style=\"display:inline;\" type=\"submit\" value=\"Delete All\" onClick=\"javascript: return window.confirm('Are you sure you want to delete all students?');\"></input>";
echo "</form>";

// write main footer and close database connection
$page->writeFooter();
$g_db->disconnect();
?>
