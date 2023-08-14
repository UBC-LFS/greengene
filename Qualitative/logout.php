<?php
require_once('includes/global.php');

// Security::logout();

$security = new Security(); // new way of initalizing class
$user = $security -> getUser(false);

// initalize new Page
$pageObj = new Page($user, 'GreenGene Login', 0);

$security-> logout();
$pageObj->redirect('login.php');

?>
