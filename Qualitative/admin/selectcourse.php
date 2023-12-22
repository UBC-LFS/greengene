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



// render table with courses the user is in
$user = (new Security) -> getUser();
$courseIDs = $user->m_courseArray;
// var_dump($userId);
?>

<?php
echo('
    <table class="listing" id="ListOfStudents">

    <tr>
    <th width="150">Name</th>
    <th width="150">Description</th>
    <th width="150">Actions</th>
    </tr>
');
?>

<?php
for ($i = 0; $i < count($courseIDs); $i++) {

    $courseInfo = $user->getCourse($courseIDs[$i]);

    echo("
        <tr>
            <td>$courseInfo->Name</td>
            <td>$courseInfo->Description</td>
        </tr>
    ");

}

echo("</table>");
?>

<?php

$page->handleErrors();


$page->writeFooter();
$g_db->disconnect();
?>

