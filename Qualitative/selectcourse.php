<?php
require_once('includes/global.php');

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object


// DATABASE CONNECTION
$g_db = new DB();

$user = (new Security) -> clearUserClass();
$user = (new Security) -> getUserTempData();
// initalize the lowest user permission level
$user = new Student($user->UserId);

// PAGE CREATION LOGIC, the 10 means anyone with privilege level 10 or lower can access page
$page = new Page($user, 'Select Courses', 0);

// write page header, including toolbar
$page->writeHeader("SelectCourses");


// render table with courses the user is in

$courseIDs = $user->m_courseArray;

$table = new Table(3);

$table->writeHeaders("Name","Description","Select Course");


for ($i = 0; $i < count($courseIDs); $i++) {
    $courseInfo = $user->getCourse($courseIDs[$i]);
  
    switch($user->m_PrivilegeLvlArray[$i])
    {
        case 10:
            $button = "<input type=\"button\" value=\"Select\" onClick=\"goUrl('/siteadmin/viewcourses.php?course=$i');\">";
            break;

        case 1:
            $button = "<input type=\"button\" value=\"Select\" onClick=\"goUrl('/admin/viewproblemlist.php?course=$i');\">";
            break;

        case 2:
            $button = "<input type=\"button\" value=\"Select\" onClick=\"goUrl('/admin/viewstudentlist.php?course=$i');\">";
            break;

        case 3:
            $button = "<input type=\"button\" value=\"Select\" onClick=\"goUrl('/student/viewprogeny.php?_userId=$user->m_userId&course=$i');\">";
            break;
    }

    if ($user->m_PrivilegeLvlArray[$i] != 10) {
        $table->writeRow($courseInfo->Name, $courseInfo->Description, $button);
    }
    else {
        $table->writeRow("Site Admin View", "Modify course details and admins", $button);
    }

}

$table->flush();

$page->handleErrors();


$page->writeFooter();
$g_db->disconnect();
?>

