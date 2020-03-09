<?php
require_once('includes/global.php');

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object
$user = Security::getUser();


// DATABASE CONNECTION
$g_db = new DB();

// FORM LOGIC
// - get form variables

// PAGE CREATION LOGIC
$page = new Page($user, 'Change Password', 0);


//call the change password function on the user object if the submit button was clicked
if(isset($_POST['oldPwd']))
{
	$oldPwd = $_POST['oldPwd'];
	$newPwd1 = $_POST['pwd1'];
	$newPwd2 = $_POST['pwd2'];

	if($user->changePassword($oldPwd, $newPwd1, $newPwd2) == true)
		$success = true;
}


// write page header, including toolbar
$page->writeHeader();

$page->handleErrors();

// now generate the content of the page

if($success)
	echo("<p>Password change successful.</p>");

// Start the form
echo("<form action=\"$PHP_SELF\" method=\"post\">");

$pwdTable = new Table(2, false, true);
$pwdTable->writeRow('Current Password', "<input type=\"password\" size=\"10\" name=\"oldPwd\">");
$pwdTable->writeRow('New Password', "<input type=\"password\" size=\"10\" maxlength=\"25\" name=\"pwd1\">");
$pwdTable->writeRow('Re-enter New Password', "<input type=\"password\" size=\"10\" maxlength=\"25\" name=\"pwd2\">");
$pwdTable->writeSpanningRow("<input type=\"submit\" name=\"submit\" value=\"Change Password\">");
//end of table
$pwdTable->flush();

// end the form
echo("</form>");

// write main footer and close database connection
$page->writeFooter();
$g_db->disconnect();
?>
