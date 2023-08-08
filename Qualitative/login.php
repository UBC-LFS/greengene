<?php
require_once('includes/global.php');

// $user = Security::getUser(false);
$security = new Security(); // new way of initalizing class
$user = $security -> getUser(false);

// initalize new Page
$pageObj = new Page($user, 'GreenGene Login', 0);
// initalize new userError
// $userErrorObj = new UserError();

// var_dump($user);

if(!empty($user))
{
	// if we're already logged in, redirect the user home
	// Page::redirectInitial($user);
	$pageObj->redirectInitial($user);
}

if(!empty($_POST['UserId']) && !empty($_POST['Pwd']))
{
	$g_db = new DB();

	// if(Security::login($_POST['UserId'], $_POST['Pwd']) == false)
	if ($security -> login($_POST['UserId'], $_POST['Pwd']) == false)
	{
		$g_db->disconnect();
		UserError::addError(306);
		// $userErrorObj->addError(306);
		var_dump($userErrorObj);
	}
	else // LOGIN accepted
	{
		$g_db->disconnect();
		// Page::redirectInitial(Security::getUser());
		// var_dump($pageObj);
		var_dump($security->getUser());
		$pageObj->redirectInitial($security->getUser());
	}
}

$page = new Page($user, 'GreenGene Login', 0);

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>GreenGene - Login</title>
</head>

<body background="includes/images/background.gif">

<form method="post" action="login.php">
<br>
<table style="margin-left:auto; margin-right:auto;">
<tr>
<td valign="top" background="includes/images/login.jpg" width="548" height="638">

<div style="position:relative;top:290px;">

<div style="position:relative;left:230px;"><input type="text" name="UserId" maxlength="10" size="13"><br>
<input type="password" name="Pwd" size="13"><br>
<input type="submit" value="Login"></div>

<div style="position:relative;left:50px;top:30px;font-family:Arial,Helvetica,sans-serif;font-size:smaller;">
<?php $page->handleErrors(); ?></div>
</div>

</td>
</tr>
</table>
</form>

<div style="text-align:center;margin-left:auto;margin-right:auto;font-size:10pt;font-family:Arial,Helvetica,sans-serif;">
GreenGene 1.0. &copy; 2005, <a href="http://sourceforge.net/projects/yellowleaf">YellowLeaf Project</a>. Released under the <a href="http://www.gnu.org/copyleft/gpl.html">GNU General Public License</a>.</div>

</body>
</html>