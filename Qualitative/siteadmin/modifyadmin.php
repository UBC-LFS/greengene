<?php
require_once('../includes/global.php');

// DATABASE CONNECTION
$g_db = new DB();

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
$user = Security::getUser();

// - get form variables
$formaction = isset($_POST['formaction'])? $_POST['formaction']: null;

// PAGE CREATION LOGIC
if(empty($_GET['userId']) && !empty($_GET['courseId']))
	$page = new Page($user, 'Add Administrator', 10);
else
	$page = new Page($user, 'Modify Administrator', 10);

// FORM LOGIC

if($formaction == 'adduser')
{
	$userId = $_POST['UserId'];
	$courseId = $_POST['CourseId'];

	// check form values
	$formOk = true;

	if($_POST['UserId'] == '')
	{
		UserError::addError(302);
		$formOk = false;
	}

	if($_POST['FirstName'] == '')
	{
		UserError::addError(303);
		$formOk = false;
	}

	if($_POST['LastName'] == '')
	{
		UserError::addError(304);
		$formOk = false;
	}

	if($formOk == true && $user->createManagementUser($userId,
		$_POST['FirstName'],
		$_POST['LastName'],
		$courseId,
		$_POST['PrivilegeLvl']) == true)
	{
		Page::redirect("managecourse.php?courseId=$courseId");
	}

	$formType = 'add';

	$FirstName = $_POST['FirstName'];
	$LastName = $_POST['LastName'];
	$PrivilegeLvl = $_POST['PrivilegeLvl'];
}
elseif($formaction == 'saveuser')
{
	$userId = $_POST['UserId'];
	$courseId = $_POST['CourseId'];

	$formOk = true;

	if($_POST['FirstName'] == '')
	{
		UserError::addError(303);
		$formOk = false;
	}

	if($_POST['LastName'] == '')
	{
		UserError::addError(304);
		$formOk = false;
	}

	if($formOk == true && $user->modifyManagementUser($_POST['UserId'],
		$_POST['FirstName'],
		$_POST['LastName'],
		$_POST['PrivilegeLvl']) == true)
	{
		Page::redirect("managecourse.php?courseId=$courseId");
	}

	$formType = 'save';

	$FirstName = $_POST['FirstName'];
	$LastName = $_POST['LastName'];
	$PrivilegeLvl = $_POST['PrivilegeLvl'];
}
elseif($formaction == 'saveuserpwd')
{
	$userId = $_POST['UserId'];
	$courseId = $_POST['CourseId'];

	if($user->modifyAdminPwd($_POST['UserId'],
		$_POST['Pwd1'],
		$_POST['Pwd2']) == true)
	{
		Page::redirect("managecourse.php?courseId=$courseId");
	}

	$formType = 'save';

	$userRs = $user->getManagementUser($userId);
	$userData = $g_db->fetch($userRs);
	$courseId = $userData->CourseId;
	$FirstName = $userData->FirstName;
	$LastName = $userData->LastName;
	$PrivilegeLvl = $userData->PrivilegeLvl;
}
elseif(empty($formaction))
{
	$userId = isset($_GET['userId'])? $_GET['userId']: null;
	$courseId = $_GET['courseId'];

	if(empty($userId))
	{
		// adding a user
		$formaction = 'adduser';
		$formType = 'add';
		$FirstName = null ;
		$LastName = null ;
		$PrivilegeLvl = null ;
	}
	else
	{
		// editing a user
		$formaction = 'saveuser';
		$formType = 'save';

		$userRs = $user->getManagementUser($userId);
		$userData = $g_db->fetch($userRs);
		$courseId = $userData->CourseId;
		$FirstName = $userData->FirstName;
		$LastName = $userData->LastName;
		$PrivilegeLvl = $userData->PrivilegeLvl;
	}
}

// write page header, including toolbar
$page->writeHeader();

// handle errors
$page->handleErrors();

// page content

if($formType == 'add')
{
	echo("<form action=\"".htmlentities($_SERVER['PHP_SELF'])."\" method=\"post\">");
	echo("<input type=\"hidden\" name=\"formaction\" value=\"adduser\">");
	echo("<input type=\"hidden\" name=\"CourseId\" value=\"$courseId\">");

	$table = new Table(2, false, true);

	$table->writeRow('User Id:',
		"<input type=\"text\" name=\"UserId\" value=\"$userId\" maxlength=\"10\">");

	$table->writeRow('First Name:',
		"<input type=\"text\" name=\"FirstName\" value=\"$FirstName\" maxlength=\"20\">");

	$table->writeRow('Last Name:',
		"<input type=\"text\" name=\"LastName\" value=\"$LastName\" maxlength=\"20\">");

	if($PrivilegeLvl == 2)
		$privs = '<option value="1">Professor<option value="2" selected>TA';
	else
		$privs = '<option value="1">Professor<option value="2">TA';

	$table->writeRow('User Type:',
		"<select name=\"PrivilegeLvl\">$privs</select>");

	$table->writeRow('Password (enter twice):',
		"<input type=\"password\" name=\"Pwd1\" maxlength=\"25\"><br>
		<input type=\"password\" name=\"Pwd2\" maxlength=\"25\">");
	$table->flush();

	echo("<p><input type=\"submit\" value=\"Add\"></p>");
	echo("</form>");
}
else
{
	echo("<form action=\"".htmlentities($_SERVER['PHP_SELF'])."\" method=\"post\">");
	echo("<input type=\"hidden\" name=\"formaction\" value=\"saveuser\">");
	echo("<input type=\"hidden\" name=\"CourseId\" value=\"$courseId\">");

	$table = new Table(2, false, true);

	$table->writeRow('User Id:',
		"<input type=\"text\" name=\"UserId\" value=\"$userId\" maxlength=\"10\" readonly>");

	$table->writeRow('First Name:',
		"<input type=\"text\" name=\"FirstName\" value=\"$FirstName\" maxlength=\"20\">");

	$table->writeRow('Last Name:',
		"<input type=\"text\" name=\"LastName\" value=\"$LastName\" maxlength=\"20\">");

	if($PrivilegeLvl == 2)
		$privs = '<option value="1">Professor<option value="2" selected>TA';
	else
		$privs = '<option value="1">Professor<option value="2">TA';

	$table->writeRow('User Type:',
		"<select name=\"PrivilegeLvl\">$privs</select>");

	$table->writeSpanningRow('<input type="submit" value="Save">');
	$table->flush();

	echo('</form>');

	$table = new Table(2, false, true);
	echo("<form action=\"".htmlentities($_SERVER['PHP_SELF'])."\" method=\"post\">");
	echo("<input type=\"hidden\" name=\"formaction\" value=\"saveuserpwd\">");
	echo("<input type=\"hidden\" name=\"CourseId\" value=\"$courseId\">");
	echo("<input type=\"hidden\" name=\"UserId\" value=\"$userId\">");

	$table->writeRow('Password (enter twice):',
		"<input type=\"password\" name=\"Pwd1\" maxlength=\"25\"><br>
		<input type=\"password\" name=\"Pwd2\" maxlength=\"25\">");

	$table->writeSpanningRow('<input type="submit" value="Change Password">');
	echo('</form>');
	$table->flush();
}

// write main footer and close database connection
$page->writeFooter();
$g_db->disconnect();
?>
