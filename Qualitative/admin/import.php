<?php
require_once('../includes/global.php');
$user = Security::getUser();
$page = new Page($user, 'Import Students', 2);
echo $user->importStudentsFromClassList();
$page->writeHeader();
?>
<!DOCTYPE html>
<body>
    <form>
        <div>
            <input>Course Subject Code</input>
            <input>Course Number Code</input>
            <input>Course Section</input>
            <input>Year</input>
            <input>Session</input>
        </div>
    </form>
</body>
<?php
$page->handleErrors();
$page->writeFooter();
?>