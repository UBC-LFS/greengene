<?php
require_once('includes/global.php');

Security::logout();

Page::redirect('login.php');

?>
