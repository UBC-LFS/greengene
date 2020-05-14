<?php
require_once('../includes/global.php');

// DATABASE CONNECTION
$g_db = new DB();

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
$user = Security::getUser();


// PAGE CREATION LOGIC
if(!empty($_GET['userId']))
	$page = new Page($user, 'Modify Administrator', 1);
else
	$page = new Page($user, 'Create Administrator', 1);

// FORM LOGIC
// - get form variables
$formaction = false;
if(isset($_POST['formaction'])) {
	$formaction = $_POST['formaction'];
}

if($formaction == 'adduser')
{
	$userId = $_POST['UserId'];

	// check form values
	$formOk = true;

	if($_POST['UserId'] == '')
	{
		UserError::addError(302);
		$formOk = false;
	}

	if($formOk == true && $user->createManagementUser($userId,
		$_POST['FirstName'],
		$_POST['LastName'],
		$_POST['PrivilegeLvl']) == true)
	{
		Page::redirect("viewadminlist.php");
	}

	$formType = 'add';

	$FirstName = $_POST['FirstName'];
	$LastName = $_POST['LastName'];
	$PrivilegeLvl = $_POST['PrivilegeLvl'];
}
elseif($formaction == 'saveuser')
{
	$userId = $_POST['UserId'];

	$formOk = true;

	if($formOk == true && $user->modifyManagementUser($_POST['UserId'],
		$_POST['FirstName'],
		$_POST['LastName'],
		$_POST['PrivilegeLvl']) == true)
	{
		Page::redirect("viewadminlist.php");
	}

	$formType = 'save';

	$FirstName = $_POST['FirstName'];
	$LastName = $_POST['LastName'];
	$PrivilegeLvl = $_POST['PrivilegeLvl'];
}
elseif($formaction == 'saveuserpwd')
{
	$userId = $_POST['UserId'];

	if($user->changeTAPwd($_POST['UserId'],
		$_POST['Pwd1'],
		$_POST['Pwd2']) == true)
	{
		Page::redirect("viewadminlist.php");
	}

	$formType = 'save';

	$userRs = $user->getManagementUser($userId);
	$userData = $g_db->fetch($userRs);
	$FirstName = $userData->FirstName;
	$LastName = $userData->LastName;
	$PrivilegeLvl = $userData->PrivilegeLvl;
}
elseif(empty($formaction))
{
	$userId = "";
	if(isset($_GET['userId'])) {
		$userId = $_GET['userId'];
	}

	if(empty($userId))
	{
		// adding a user
		$formaction = 'adduser';
		$formType = 'add';
		$FirstName = '';
		$LastName = '';
		$PrivilegeLvl = '';
	}
	else
	{
		// editing a user
		$formaction = 'saveuser';
		$formType = 'save';

		$userRs = $user->getManagementUser($userId);
		$userData = $g_db->fetch($userRs);
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

	$table = new Table(2, false, true);

	$table->writeRow('CWL Username:',
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

	$table->flush();

	echo("<p><input type=\"submit\" value=\"Add\"></p>");
	echo("</form>");
}
else
{
	echo("<form action=\"".htmlentities($_SERVER['PHP_SELF'])."\" method=\"post\">");
	echo("<input type=\"hidden\" name=\"formaction\" value=\"saveuser\">");

	$table = new Table(2, false, true);

	$table->writeRow('CWL Username:',
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
	echo("<input type=\"hidden\" name=\"UserId\" value=\"$userId\">");

	echo('</form>');
	$table->flush();
}

// write main footer and close database connection
$page->writeFooter();
$g_db->disconnect();
?>
