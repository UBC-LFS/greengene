<?php
require_once('../includes/global.php');

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object

// $user = (new Security) -> getUser();
$user = (new Security)->getUser(); // php8

// DATABASE CONNECTION
$g_db = new DB();

// PAGE CREATION LOGIC
$page = new Page($user, 'Select Courses', 1);

// write page header, including toolbar
$page->writeHeader();



$page->handleErrors();


$page->writeFooter();
$g_db->disconnect();
?>
