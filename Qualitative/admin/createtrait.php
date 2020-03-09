<?php
require_once('../includes/global.php');

// SESSION
$user = Security::getUser();

// PAGE CREATION LOGIC
$page = new Page($user, 'Create Trait', 1);

// DATABASE CONNECTION
$g_db = new DB();

if(!empty($_POST['traitName']))
{
	$traitId = $user->createTrait($_POST['traitName']);
	if($traitId != false)
		Page::redirect("modifytrait.php?traitId=$traitId");
}

// write page header, including toolbar
$page->writeHeader();
$page->handleErrors();

echo("<form action=\"$PHP_SELF\" method=\"post\">");

$table = new Table(2, false, true);
$table->writeRow('Trait Name', '<input type="text" name="traitName">');
$table->writeSpanningRow('<input type="submit" value="Create">');
$table->flush();

echo("</form>");


$page->writeFooter();
$g_db->disconnect();
?>
