<?php
require_once('../includes/global.php');

// DATABASE CONNECTION
$g_db = new DB();

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
// $user = Security::getUser();
$user = (new Security)->getUser(); // php8

// PAGE CREATION LOGIC
$page = new Page($user, 'Modify My Account', 10);


// FORM LOGIC
// - get form variables
$formaction = false;
if(isset($_POST['formaction'])) {
	$formaction = $_POST['formaction'];
}

if($formaction == 'saveuser')
{
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

	if($formOk == true && $user->modifyMasterAdmin($user->m_userId,
		$_POST['FirstName'],
		$_POST['LastName']) == true)
	{
		$user->m_firstName = $_POST['FirstName'];
		$user->m_lastName = $_POST['LastName'];
		$_SESSION['userSession'] = $user;

		Page::redirect("myaccount.php");
	}

	$formType = 'save';

	$FirstName = $_POST['FirstName'];
	$LastName = $_POST['LastName'];
}
else
{
	$userId = $user->m_userId;
	$FirstName = $user->m_firstName;
	$LastName = $user->m_lastName;
}

// write page header, including toolbar
$page->writeHeader();

// handle errors
$page->handleErrors();

// page cont
echo("<form action=\"".htmlentities($_SERVER['PHP_SELF'])."\" method=\"post\">");
echo("<input type=\"hidden\" name=\"formaction\" value=\"saveuser\">");

$table = new Table(2, false, true);

$table->writeRow('CWL Username:',
	"<input type=\"text\" name=\"UserId\" value=\"$userId\" maxlength=\"10\" readonly>");

$table->writeRow('First Name:',
	"<input type=\"text\" name=\"FirstName\" value=\"$FirstName\" maxlength=\"20\">");

$table->writeRow('Last Name:',
	"<input type=\"text\" name=\"LastName\" value=\"$LastName\" maxlength=\"20\">");

$table->writeSpanningRow('<input type="submit" value="Save">');
$table->flush();

echo('</form>');

// write main footer and close database connection
$page->writeFooter();
$g_db->disconnect();
?>
