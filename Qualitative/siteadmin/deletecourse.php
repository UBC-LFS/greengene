<?php
require_once('../includes/global.php');

// $user = Security::getUser();
$user = (new Security) -> getUser(); // PHP 8
$page = new Page($user, 'Delete Course', 10);
$g_db = new DB();

$courseId = isset($_GET['courseId']) ? $_GET['courseId'] : null;
$formaction = isset($_POST['formaction']) ? $_POST['formaction'] : null;

$page->writeHeader();
$page->handleErrors();

if ($formaction != null ) {
        $user->deleteCourse($formaction);
        Page::redirect('viewcourses.php');
}

echo("<h3>The following information will be deleted</h3>");

echo("<div>");
echo("<p>Course: </p>");
$table = new Table(2);
$table->writeHeaders('Course Name', 'Course Description');
$course = $user->getCourse($courseId);
$row =  $g_db->fetch($course);
$table->writeRow($row->Name, $row->Description);
$table->flush();
echo("</div>");

echo("<div>");
echo("<p>Users: </p>");
$table = new Table(4);
$table->writeHeaders('User ID', 'First Name', 'Last Name', 'Role');
$users = $user->getUsers($courseId);
$priv = array(1 => 'Professor', 2 => 'TA', 3=> "Student");
while($row = $g_db->fetch($users)) {
    $table->writeRow($row->UserId, $row->FirstName, $row->LastName, $priv[$row->PrivilegeLvl]);
};
$table->flush();
echo("</div>");

echo("<div>");
echo("<p>Problems: </p>");
$table = new Table(2);
$table->writeHeaders('Problem Name', 'Description');
$users = $user->getProblems($courseId);
while($row = $g_db->fetch($users)) {
    $table->writeRow($row->Name, $row->Description);
};
$table->flush();
echo("</div>");

echo("<div>");
echo("<p>Traits: </p>");
$table = new Table(1);
$table->writeHeaders('Trait');
$users = $user->getTraits($courseId);
while($row = $g_db->fetch($users)) {
    $table->writeRow($row->Name);
};
$table->flush();
echo("</div>");

echo("<form action=\"".htmlentities($_SERVER['PHP_SELF'])."\" method=\"post\">");
echo("<p><input type=\"hidden\" name=\"formaction\" value=\"$courseId\"></p>");
echo("<p><input type=\"submit\" name=\"something\" value=\"Delete\">");
echo("</form>");
?>