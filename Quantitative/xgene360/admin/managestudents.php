<?php

/*
* include necessary files
*/

require_once( '../includes/global.inc.php' );
require_once( '../includes/classes/db/studentmanager.class.php' );
require_once( '../includes/classes/db/coursemanager.class.php' );
require_once( '../includes/classes/db/problemmanager.class.php' );
require_once( '../includes/classes/db/assignstudentmanager.class.php' );
require_once( '../includes/classes/handler/ldaphandler.class.php' );

/*
* initialize common stuff
*/

$g_obj_db = null;
$g_obj_lock = null;
$g_str_serial = null;
$g_obj_user = null;

PageHandler::initialize();
PageHandler::check_permission( array( UP_ADMINISTRATOR, UP_PROFESSOR, UP_TA ) );

$g_obj_student_manager = new StudentManager( $g_obj_user, $g_obj_db );
$g_obj_course_manager = new CourseManager( $g_obj_user, $g_obj_db );
$g_obj_problem_manager = new ProblemManager( $g_obj_user, $g_obj_db );
$g_obj_assign_student_manager = new AssignStudentManager( $g_obj_user, $g_obj_db );

process_post();

/*
* set header stuff
*/

$g_str_page_title = "Manage Students";
$g_arr_scripts = array( 'managestudents.js', 'commonadmin.js' );
$g_arr_nav_links = $g_arr_nav_defined_links[$g_obj_user->int_privilege];
$g_str_script_block = "xgene360_cu.using( 'sortabletable' )";

require_once( '../includes/header.inc.php' );

if ( $g_bln_display_content )
{
?>

<!-- Start Content -->
<div>

  <form id="ManageStudentsForm" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" enctype="multipart/form-data">

    <strong>
      Click on a student to edit specific student information. 
      <br />To delete a student or several students, click on the checkbox beside each student to be deleted and press 'Delete Selected'. 
      <br />You can also import or export lists of students.
    </strong>    
    
    <br /><br />
    
<?php

if ( $g_obj_user->int_privilege != UP_TA )
{
?>              

    <table class="format" width="100%">
      <tr>
        <td>
          <input class="buttoninput" type="button" value="Add New" name="Command"  onclick="displayCreateStudent();"/>&nbsp;
          <input class="buttoninput" type="button" value="Import Class" name="Command"  onclick="displayImportStudent();"/>&nbsp;
          <input class="buttoninput" type="submit" value="Export Selected" name="Command" onclick="return validateStudentSelection();" />&nbsp;    
          <input class="buttoninput" type="submit" value="Delete Selected" name="Command" onclick="return validateDeleteStudent();" />
        </td>
        
        <td align="right">

          <table class="format">

            <tr>
              <td>
                <select name="SelectAssign" id="SelectAssign" onchange="xgene360_cu.selectDisplay('SelectAssign', ['TdSelectCourse', 'TdSelectProblem', 'TdSelectNone']);">

                  <option selected="selected" value="TdSelectNone">&nbsp;Choose type to assign:&nbsp;&nbsp;&nbsp;&nbsp;</option>
                  <option value="TdSelectNone">&nbsp;----------</option>
                  <option value="TdSelectNone">&nbsp;</option>
                  <option value="TdSelectCourse">&nbsp;Course</option>
                  <option value="TdSelectProblem">&nbsp;Problem</option>

                </select>
                &nbsp;
              </td>

              <td id="TdSelectNone">
                <select name="SelectNone" id="SelectNone">

                  <option selected="selected" value="">&nbsp;Select type first&nbsp;</option>

                </select>
                &nbsp;
              </td>

              <td id="TdSelectCourse" style="display: none;">
                <select name="SelectedCourse" id="SelectedCourse">
                
<?php
	
echo( '<option selected="selected" value="">&nbsp;Assign to course:&nbsp;&nbsp;&nbsp;&nbsp;</option>' );
echo( '<option value="">&nbsp;----------</option>' );
echo( '<option value="">&nbsp;</option>' );

$res_courses = $g_obj_course_manager->view_courses();

for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $res_courses ); ++$i )
{
	$res_row = $g_obj_db->fetch( $res_courses );
	
	echo( '<option value="' . htmlspecialchars( $res_row->CourseId ) . '">&nbsp;&nbsp;&nbsp;&nbsp;' . htmlspecialchars( $res_row->Name ) . '</option>' . "\n" );
}

?>

                </select>
                &nbsp;
              </td>

              <td id="TdSelectProblem" style="display: none;">
                <select name="SelectedProblem" id="SelectedProblem">
                
<?php

echo( '<option selected="selected" value="">&nbsp;Assign to problem:&nbsp;&nbsp;&nbsp;&nbsp;</option>' );         
echo( '<option value="">&nbsp;----------</option>' );   
echo( '<option value="">&nbsp;</option>' );

$res_problems = $g_obj_problem_manager->view_problems();

for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $res_problems ); ++$i )
{
	$res_row = $g_obj_db->fetch( $res_problems );
	
	echo( '<option value="' . htmlspecialchars( $res_row->problem_id ) . '">&nbsp;' . htmlspecialchars( $res_row->problem_name ) . '&nbsp;</option>' . "\n" );
}

?>

                </select>
                &nbsp;
              </td>

              <td>
                <input class="buttoninput" type="submit" name="Command" value="Assign" onclick="return validateAssignStudentsToACourseOrAProblem();" />
              </td>
            </tr>

          </table>

        </td>
      </tr>

    </table>

<?php
}
?>
    <table class="listing" id="ListOfStudents">

      <tr>
        <th width="50"><input type="checkbox" id="UserIdSelectionToggle" onclick="xgene360_cu.checkAll( this, 'StudentId[]' );" /></th>
		<th width="150">CWL Username</th>
        <th width="150">First Name</th>
        <th width="150">Last Name</th>
		<th></th>
      </tr>

<?php

$res_students = $g_obj_student_manager->view_students();

for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $res_students ); ++$i )
{
	$res_row = $g_obj_db->fetch( $res_students );
	
	echo( '<tr onclick="openStudentDetail( \'' . htmlspecialchars( $res_row->UserId, ENT_QUOTES ) . '\' );" onmouseover="hightlightSelectedRow( this, true );" onmouseout="hightlightSelectedRow( this, false );">' . "\n" );
	echo( '<td onmouseover="xgene360_cu.stopPropagation( event );" onclick="xgene360_cu.stopPropagation( event );"><input type="checkbox" name="StudentId[]" value="' . htmlspecialchars( $res_row->UserId ) . '" /></td>' . "\n" );
	echo( '<td>' . htmlspecialchars( $res_row->UserId ) . '</td>' . "\n" );
	echo( '<td>' . htmlspecialchars( $res_row->FirstName ) . '</td>' . "\n" );
	echo( '<td>' . htmlspecialchars( $res_row->LastName ) . '</td>' . "\n" );
	echo( '<td> </td>'."\n");
	echo( '</tr>' . "\n" );
}
    
?>  
              
    </table>

<?php

if ( $g_obj_user->int_privilege != UP_TA )
{
?>              

    <input class="buttoninput" type="button" value="Add New" name="Command" onclick="displayCreateStudent();" />&nbsp;
    <input class="buttoninput" type="button" value="Import Class" name="Command" onclick="displayImportStudent();" />&nbsp;
    <input class="buttoninput" type="submit" value="Export Selected" name="Command" onclick="return validateStudentSelection();" />&nbsp;
    <input class="buttoninput" type="submit" value="Delete Selected" name="Command" onclick="return validateDeleteStudent();" />

<?php
}
?>

    <div id="CreateStudentDiv" style="display: none">

      <br /><br />

      <table class="box">

        <tr>
          <th>Create Student</th>
        </tr>

        <tr>
          <td>

            <table>

              <tr>
                <td width="125">First Name:</td>
                <td><input class="textinput" type="text" name="FirstName" id="FirstName" value="<?= htmlspecialchars( PageHandler::write_post_value_if_failed( 'FirstName' ) ) ?>" onkeypress="xgene360_cu.checkDefaultSubmitButton( event, 'CommandCreate' );" /></td>
              </tr>

              <tr>
                <td>Last Name:</td>
                <td><input class="textinput" type="text" name="LastName" id="LastName" value="<?= htmlspecialchars( PageHandler::write_post_value_if_failed( 'LastName' ) ) ?>" onkeypress="xgene360_cu.checkDefaultSubmitButton( event, 'CommandCreate' );" /></td>
              </tr>

              <tr>
                <td>CWL Username:</td>
                <td><input class="textinput" type="text" name="Username" id="Username" value="<?= htmlspecialchars( PageHandler::write_post_value_if_failed( 'Username' ) ) ?>" onkeypress="xgene360_cu.checkDefaultSubmitButton( event, 'CommandCreate' );" /></td>
              </tr>

              <tr>
                <td colspan="2" align="right">
                  <input class="buttoninput" type="submit" name="Command" id="CommandCreate" value="Create" onclick="return validateCreateStudentForm();" />
                  &nbsp;<input class="buttoninput" type="reset" name="Reset" value="Reset" onclick="return resetCreateStudentForm();" />
                </td>
              </tr>

            </table>

          </td>
        </tr>

      </table>

    </div>

	<div id="ImportStudentDiv" style="display: none">
	<!--  TODO: change to input field-->
      <br /><br />

      <table class="box">

        <tr>
		  <th>Import Class </th>
        </tr>

        <tr>
			<td>Course Subject Code</td>
			<td><input class="textinput" type="text" id="CourseSubjectCode" name="CourseSubjectCode" placeholder="APBI" value="<?= htmlspecialchars( PageHandler::write_post_value_if_failed( 'CourseSubjectCode' ) ) ?>" ></input></td>
		</tr>

        <tr>
			<td>Course Number</td>
			<td><input class="textinput" type="text" id='CourseNumber' name="CourseNumber" placeholder="318" value="<?= htmlspecialchars( PageHandler::write_post_value_if_failed( 'CourseNumber' ) ) ?>"></input></td>
		</tr>
 
		<tr>
			<td>Course Section</td>
			<td><input class="textinput" type="text" id='CourseSection' name="CourseSection" placeholder="001" value="<?= htmlspecialchars( PageHandler::write_post_value_if_failed( 'CourseSection' ) ) ?>"></input></td>
		</tr>
   
		<tr>
			<td>Year</td>
			<td><input class="textinput" type="text" id='Year' name="Year" placeholder="2019" value="<?= htmlspecialchars( PageHandler::write_post_value_if_failed( 'Year' ) ) ?>"></input></td>
		</tr>

        <tr>
			<td>Session</td>
			<td>
				<select name="Session">
					<option value="W">Winter</option>
					<option value="S">Summer</option>

				</select>
			</td>
		</tr>

		<tr>
			<td colspan="2" align="right">
				<input class="buttoninput" type="submit" name="Command" value="Import" onclick="return validateImportStudent();"/>
			</td>
		</tr>
      </table>

    </div>
		<input type="hidden" name="SerialId" id="SerialId" value="<?= $g_str_serial ?>"/>
  </form>

</div>
<!-- End Content -->

<?php 
}

require_once( '../includes/footer.inc.php' ); 

$g_obj_db->disconnect();

/**  Function: void process_post()
*    ---------------------------------------------------------------- 
*    Purpose:           Call appropriate functions based on the POST
*                       command
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function process_post()
{
	global $g_obj_lock;
	
	if ( isset( $_POST['Command'] ) && $g_obj_lock->page_lock( PageHandler::get_post_value( 'SerialId' ) ) )
	{
		$str_command = $_POST['Command'];
	  
		switch ( $str_command )
		{
			case 'Create':
			{
				on_create_handler();
			}	
			break;
			  	
			case 'Delete Selected':
			{
				on_delete_handler();
			}
			break;
				
			case 'Assign':
			{     
				on_assign_handler();
			}
			break; 

			case 'Import':
			{
				on_import_handler();
			}
			break;
			
			case 'Export Selected':
			{
				on_export_handler();
			}
			break;
			
			default:
			{
				MessageHandler::add_message( MSG_ERROR, "Unknown Command" );
			}
			break;
		}
	}
}

/**  Function: void on_create_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process creating the student
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_create_handler()
{
	global $g_obj_student_manager;
	
	$cwl_username = PageHandler::get_post_value( 'Username' );
	$str_first_name = PageHandler::get_post_value( 'FirstName' );
	$str_last_name = PageHandler::get_post_value( 'LastName' );
	
	// verify the input
	if (strlen( $cwl_username ) == 0 )
	{
		MessageHandler::add_message( MSG_FAIL, 'Please enter a valid CWL Username' );
		return;
	}
	
	$str_first_name = !isset($str_first_name) ? " " : $str_first_name;
	$str_last_name = !isset($str_last_name) ? " " : $str_last_name;
		
	if ( $g_obj_student_manager->create_user( $cwl_username, UP_STUDENT,  $str_first_name, $str_last_name ) )
	{
		MessageHandler::add_message( MSG_SUCCESS, 'Successfully created an account for Student with CWL username: ' . $cwl_username);
	}
	
	else
	{
		MessageHandler::add_message( MSG_FAIL, 'Failed to create an account for Student with CWL username: "' .$cwl_username );
	}
}

/**  Function: void on_delete_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process deleting the students
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_delete_handler()
{
	global $g_obj_student_manager;
	
	$arr_student_list = PageHandler::get_post_value( 'StudentId' );
	
	if ( $arr_student_list == null )
	{
		MessageHandler::add_message( MSG_FAIL, "Please select at least one student" );
		return;
	}
	
	$arr_success = array();
	$arr_fail = array();
	
	for ( $i = 0; $i < count($arr_student_list); ++$i) 
	{
		$userId = $arr_student_list[$i];
		if ( $g_obj_student_manager->delete_user( $userId ) )
		{
			array_push( $arr_success, $userId );
		}
		
		else
		{
			array_push( $arr_fail, $userId );
		}	
	}
	
	if ( count( $arr_success ) != 0 )
	{
		$str_message = PageHandler::display_users_cwl( 'Successfully deleted students with CWL Username', $arr_success );
		
		MessageHandler::add_message( MSG_SUCCESS, $str_message );
	}
	
	if ( count( $arr_fail ) != 0 )
	{
		$str_message = PageHandler::display_users_cwl( 'Failed to delete students with CWL Username', $arr_fail );
		
		MessageHandler::add_message( MSG_FAIL, $str_message );
	}
}

/**  Function: void on_assign_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process assigning the students
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_assign_handler()
{
	global $g_obj_student_manager, $g_obj_assign_student_manager;
	
	$arr_student_list = PageHandler::get_post_value( 'StudentId' );
	$int_selected_course_id = PageHandler::get_post_value( 'SelectedCourse' );
	$int_selected_problem_id = PageHandler::get_post_value( 'SelectedProblem' );
	
	if ( $arr_student_list == null || ( strlen( $int_selected_course_id ) == 0 && strlen( $int_selected_problem_id ) == 0 ) )
	{
		MessageHandler::add_message( MSG_FAIL, "Please select at least one student and select a course or a problem" );
		return;
	}
	
	$arr_student_names = $g_obj_student_manager->user_names_list( $arr_student_list );
	
	$arr_success = array();
	$arr_fail = array();

	if ( strlen( $int_selected_course_id ) != 0 )
	{
		for ( $i = 0; $i < count( $arr_student_names ); ++$i )
		{
			if ( $g_obj_assign_student_manager->assign_student_to_course( $arr_student_names[$i][0], $int_selected_course_id ) )
			{
				array_push( $arr_success, $arr_student_names[$i][0] );
			}
			
			else
			{
				array_push( $arr_fail, $arr_student_names[$i][0] );
			}
		}
	}
	
	else
	{
		for ( $i = 0; $i < count( $arr_student_names ); ++$i )
		{
			if ( $g_obj_assign_student_manager->assign_student_to_problem( $arr_student_names[$i][0], $int_selected_problem_id ) )
			{
				array_push( $arr_success, $arr_student_names[$i][0] );
			}
			
			else
			{
				array_push( $arr_fail, $arr_student_names[$i][0] );
			}
		}
	}
	
	if ( count( $arr_success ) != 0 )
	{
		$str_message = PageHandler::display_users_cwl( 'Successfully assigned', $arr_success );
		
		MessageHandler::add_message( MSG_SUCCESS, $str_message );
	}
	
	if ( count( $arr_fail ) != 0 )
	{
		$str_message = PageHandler::display_users_cwl( 'Failed to assign', $arr_fail );
		
		MessageHandler::add_message( MSG_FAIL, $str_message );
	}
}

/**  Function: void on_import_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process importing the students list
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_import_handler()
{
	$courseSubjectCode = PageHandler::get_post_value('CourseSubjectCode');
	$courseNumber = PageHandler::get_post_value('CourseNumber');
	$courseSection = PageHandler::get_post_value('CourseSection');
	$year = PageHandler::get_post_value('Year');
	$session = PageHandler::get_post_value('Session');

	$payload = ['subjectCode' => $courseSubjectCode,
			'courseNumber' => $courseNumber,
			'section' => $courseSection,
			'year' => $year,
			'session' => $session]; 

	$result = LDAPHandler::importClassList($payload);
	LDAPHandler::createUserFromLDAPResult($result);
}

/**  Function: void on_export_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process exporting the students list
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_export_handler()
{
	$arr_student_list = PageHandler::get_post_value( 'StudentId' );
	
	if ( $arr_student_list == null )
	{
		MessageHandler::add_message( MSG_FAIL, "Please select at least one student" );
		return;
	}
	
	else
	{
		FileHandler::export_student_list( $arr_student_list );
	}
}

?>
