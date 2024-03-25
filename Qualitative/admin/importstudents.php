<?php
require_once('../includes/global.php');
$user = (new Security) -> getUser();
$g_db = new DB();
$page = new Page($user, 'Import Students', 2);
$page->writeHeader();

$page->handleErrors();

$formaction = isset($_POST['formaction']) ? $_POST['formaction'] : false;
$classList = [];

if ($formaction == "import") {
    $payload = array('subjectCode' => $_POST['subjectCode'],
        'courseNumber' => $_POST['courseNumber'],
        'section' => $_POST['section'],
        'year' => $_POST['year'],
        'session' => $_POST['session']);
    $classList = $user->importClassList($payload);
    if ($classList !== null) {
        if (!count($classList)) {
            echo "<p style='color:red;' >Class has no students. Please try another Class.</p>";
        }
    }
} else if ($formaction == "add"){
    
    if (isset($_POST['removeExisting'])) {
        $user->deleteAllStudents();
    }

    $studentUserId = isset($_POST['data']) ? $_POST['data'] : false;
    if ($studentUserId) {
        for ($i = 0; $i < count($studentUserId); $i++) {
            $user->createStudent($studentUserId[$i] ," " ," ");
        }
    }
    $page -> redirect("viewstudentlist.php");
}
 
echo "<form action=\"".htmlentities($_SERVER['PHP_SELF'])."\" method=\"post\">";
echo "<input type=\"hidden\" name=\"formaction\" value=\"import\">";
$table = new Table(2, false, true);
$table->writeRow('Course Subject Code', "<input required type=\"text\" name=\"subjectCode\" maxlength=\"4\" placeholder=\"APBI\">");
$table->writeRow('Course Number', "<input required type=\"text\" name=\"courseNumber\" maxlength=\"4\" placeholder=\"318\">");
$table->writeRow('Course Section', "<input requiredtype=\"text\" name=\"section\" maxlength=\"4\" placeholder=\"001\">");
$table->writeRow('Year', "<input required type=\"number\" name=\"year\" maxlength=\"4\" placeholder=\"2019\">");
$table->writeRow('Session', "<select name=\"session\"> <option value=\"W\">Winter</option> <option value=\"S\">Summer</option></select>");
$table->flush();
echo '<br/>';
echo '<input type="submit" value="import">';
echo "</form>";

if ($classList != null && count($classList)) {
    // echo 'printing class list';
    
    echo "<p>Class List for ".$payload['subjectCode'].$payload['courseNumber']." Section ".$payload['section']." Year ".$payload['year'].$payload['session']."</p>";
    echo "<p>Number of Students: ".count($classList)."</p>";
    echo "<form action=\"".htmlentities($_SERVER['PHP_SELF'])."\" method=\"post\">";
    echo "<input type=\"hidden\" name=\"formaction\" value=\"add\">";
    $table = new Table(2, true, true);
    $table->writeHeaders('','CWL Username');
    
    for ($i = 0; $i < count($classList); $i++) {
        $cwl = $classList[$i];
        $table->writeRow('', "<input type=\"hidden\" name=\"data[$i]\" value=\"$cwl\">".$cwl."</input>");
    }
    
    $table->flush();
    echo '<br/>';
    echo '<br/>';
    echo '<input type="checkbox" name="removeExisting" value="remove">';
    echo '<label> Remove Existing Students</label>';
    echo '<br/>';
    echo '<br/>';
    echo '<input type="submit" value="Add Students ">';
    echo "</form>";
}

$page->writeFooter();
?>
