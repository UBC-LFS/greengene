<?php
require_once('includes/global.php');

// SESSION
// - check session (session hander should redirect user if not logged in)
// - get user object

// $user = (new Security) -> getUser();
// $user = (new Security)->getUser(); // php8

// DATABASE CONNECTION
$g_db = new DB();

$user = (new Security) -> getUserTempData();
// initalize the lowest user permission level
$user = new Student($user->UserId);

// If unknown privilege level, clear session??



// PAGE CREATION LOGIC, the 10 means anyone with privilege level 10 or lower can access page
$page = new Page($user, 'Select Courses', 0);

// write page header, including toolbar
$page->writeHeader("SelectCourses");


// render table with courses the user is in


// var_dump($user);

$courseIDs = $user->m_courseArray;

$table = new Table(3);

$table->writeHeaders("Name","Description","Select Course");

// function changeUserClass($privilegeLvl, $url) {
//     alert("going to change user class -> go url");
//     goUrl($url);
// }

// if (isset($_POST['courseIndex']) && isset($_POST['url'])) {

// }

for ($i = 0; $i < count($courseIDs); $i++) {

    // if ($user->m_privilegeLvl != 10) {
    $courseInfo = $user->getCourse($courseIDs[$i]);
    // }

    switch($user->m_PrivilegeLvlArray[$i])
    {
        // Do we want admins to have more access?
        case 10:
            $button = "<input type=\"button\" value=\"Select\" onClick=\"goUrl('/siteadmin/viewcourses.php?course=$i');\">";
            // $button = "<input type=\"button\" value=\"Select\" name=\"$i\">";

            // Page::redirect('siteadmin/viewcourses.php');
            break;

        case 1:
            $button = "<input type=\"button\" value=\"Select\" onClick=\"goUrl('/admin/viewproblemlist.php?course=$i');\">";
            // Page::redirect('admin/viewproblemlist.php');
            break;

        case 2:
            $button = "<input type=\"button\" value=\"Select\" onClick=\"goUrl('/admin/viewstudentlist.php?course=$i');\">";
            // Page::redirect('admin/viewstudentlist.php');
            break;

        case 3:
            $button = "<input type=\"button\" value=\"Select\" onClick=\"goUrl('/student/viewprogeny.php?_userId=$user->m_userId&course=$i');\">";
            // Page::redirect('student/viewprogeny.php');
            break;
    }

    if ($user->m_PrivilegeLvlArray[$i] != 10) {
        $table->writeRow($courseInfo->Name, $courseInfo->Description, $button);
    }
    else {
        $table->writeRow("Site Admin View", "Modify course details and admins", $button);
    }

    // Display header?
    // Get all courses for site admins
    // if site admin, display all courses
    // if ($user->m_privilegeLvl == 10) {
    //     $courseIdArrays = array();
    //     $privilegeLvlArrays = array();

    //     $courses = $user->getCourses();
    //     $index = 0;
    //     while($row = $g_db->fetch($courses)) {
    //         $button = "<input type=\"button\" value=\"Select\" onClick=\"goUrl('viewproblemlist.php?course=$index');\">";
    //         $table->writeRow($row->Name, $row->Description, $button);
            
    //         $courseIdArrays[$index] = strval($row->CourseId);
    //         $privilegeLvlArrays[$index] = '1';
    //         $index++;
    //     }

    //     // add default courseId and privilege level
    //     // $user->m_courseId = '1';
    //     // $user->m_privilegeLvl = '1';
    //     // add all course ids and privilege levels to the admin
    //     $user->m_courseArray = $courseIdArrays;
    //     $user->m_PrivilegeLvlArray = $privilegeLvlArrays;
    // }

}

// echo("</table>");

$table->flush();

$page->handleErrors();


$page->writeFooter();
$g_db->disconnect();
?>

