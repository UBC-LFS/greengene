<?php
require_once('../includes/global.php');

// DATABASE CONNECTION
$g_db = new DB();

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
$user = Security::getUser();


// PAGE CREATION LOGIC
$page = new Page($user, 'Course Administrators', 1);


// FORM LOGIC
// - get form variables
$formaction = $_POST['formaction'];

if($formaction == 'deleteuser')
{
	$delUser = $_POST['delUser'];

	if(count($delUser) > 0)
		foreach($delUser as $userId)
			$user->deleteManagementUser($userId);
}

// write page header, including toolbar
$page->writeHeader();

// DATA LOGIC
$users = $user->getManagementUsers();

// handle errors
$page->handleErrors();

// page content
echo("<form action=\"$PHP_SELF\" method=\"post\">");
echo("<input type=\"hidden\" name=\"formaction\" value=\"deleteuser\">");

$table = new Table(6, true, true);
$table->writeHeaders('', 'User Id', 'First Name', 'Last Name', 'User Type', '');

$priv = array(1 => 'Professor', 2 => 'TA');

while($row = $g_db->fetch($users))
{
	$table->writeRow("<input type=\"checkbox\" name=\"delUser[]\" value=\"$row->UserId\">",
		$row->UserId,
		$row->FirstName,
		$row->LastName,
		$priv[$row->PrivilegeLvl],
		"<input type=\"button\" value=\"Modify\" onClick=\"goUrl('modifyadmin.php?userId=$row->UserId');\">");
}
$table->flush();

echo("<p><input type=\"submit\" value=\"Delete Selected\"> &nbsp;&nbsp;
<input type=\"button\" value=\"Create Admin\" onClick=\"goUrl('modifyadmin.php');\"></p>");
echo("</form>");

$page->writeFooter();
$g_db->disconnect();
?>
