<?php
require_once('../includes/global.php');

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object

// $user = (new Security) -> getUser();
$user = (new Security)->getUser(); // php8

// DATABASE CONNECTION
$g_db = new DB();

// PAGE CREATION LOGIC, the 10 means anyone with privilege level 10 or lower can access page
$page = new Page($user, 'Select Courses', 3);

// write page header, including toolbar
$page->writeHeader("SelectCourses");


// render table with courses the user is in
$user = (new Security) -> getUser();
$courseIDs = $user->m_courseArray;

$table = new Table(3);

$table->writeHeaders("Name","Description","Select Course");

for ($i = 0; $i < count($courseIDs); $i++) {

    if ($user->m_privilegeLvl != 10) {
        $courseInfo = $user->getCourse($courseIDs[$i]);
    }

    // var_dump($courseInfo);

    switch($user->m_privilegeLvl)
    {
        // Do we want admins to have more access?
        case 10:
            $button = "<input type=\"button\" value=\"Select\" onClick=\"goUrl('../siteadmin/viewcourses.php');\">";
            // Page::redirect('siteadmin/viewcourses.php');
            break;

        case 1:
            $button = "<input type=\"button\" value=\"Select\" onClick=\"goUrl('viewproblemlist.php?course=$i');\">";
            // Page::redirect('admin/viewproblemlist.php');
            break;

        case 2:
            $button = "<input type=\"button\" value=\"Select\" onClick=\"goUrl('viewstudentlist.php?course=$i');\">";
            // Page::redirect('admin/viewstudentlist.php');
            break;

        case 3:
            $button = "<input type=\"button\" value=\"Select\" onClick=\"goUrl('../student/viewprogeny.php?course=$i');\">";
            // Page::redirect('student/viewprogeny.php');
            break;
    }

    if ($user->m_privilegeLvl != 10) {
        $table->writeRow($courseInfo->Name, $courseInfo->Description, $button);
    }
    else {
        $table->writeRow("Site Admin View", "Modify course details and admins", $button);
    }
}

// echo("</table>");

$table->flush();

$page->handleErrors();


$page->writeFooter();
$g_db->disconnect();
?>

