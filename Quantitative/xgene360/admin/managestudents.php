<?php

/*
* include necessary files
*/

require_once( '../includes/global.inc.php' );
require_once( '../includes/classes/db/studentmanager.class.php' );
require_once( '../includes/classes/db/coursemanager.class.php' );
require_once( '../includes/classes/db/problemmanager.class.php' );
require_once( '../includes/classes/db/assignstudentmanager.class.php' );

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
          <input class="buttoninput" type="button" value="Create New" name="Command"  onclick="displayCreateStudent();"/>&nbsp;
          <input class="buttoninput" type="button" value="Import List" name="Command"  onclick="displayImportStudent();"/>&nbsp;
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
        <th width="150">Student Number</th>
        <th width="150">First Name</th>
        <th width="150">Last Name</th>
        <th>Username</th>
      </tr>

<?php

$res_students = $g_obj_student_manager->view_students();

for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $res_students ); ++$i )
{
	$res_row = $g_obj_db->fetch( $res_students );
	
	echo( '<tr onclick="openStudentDetail( \'' . htmlspecialchars( $res_row->UserId, ENT_QUOTES ) . '\' );" onmouseover="hightlightSelectedRow( this, true );" onmouseout="hightlightSelectedRow( this, false );">' . "\n" );
	echo( '<td onmouseover="xgene360_cu.stopPropagation( event );" onclick="xgene360_cu.stopPropagation( event );"><input type="checkbox" name="StudentId[]" value="' . htmlspecialchars( $res_row->UserId ) . '" /></td>' . "\n" );
	echo( '<td>' . htmlspecialchars( $res_row->StudentNum ) . '</td>' . "\n" );
	echo( '<td>' . htmlspecialchars( $res_row->FirstName ) . '</td>' . "\n" );
	echo( '<td>' . htmlspecialchars( $res_row->LastName ) . '</td>' . "\n" );
	echo( '<td>' . htmlspecialchars( $res_row->UserId ) . '</td>' . "\n" );
	echo( '</tr>' . "\n" );
}
    
?>  
              
    </table>

<?php

if ( $g_obj_user->int_privilege != UP_TA )
{
?>              

    <input class="buttoninput" type="button" value="Create New" name="Command" onclick="displayCreateStudent();" />&nbsp;
    <input class="buttoninput" type="button" value="Import List" name="Command" onclick="displayImportStudent();" />&nbsp;
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
                <td>Student Number:</td>
                <td><input class="textinput" type="text" name="StudentNumber" id="StudentNumber" value="<?= htmlspecialchars( PageHandler::write_post_value_if_failed( 'StudentNumber' ) ) ?>" onkeypress="xgene360_cu.checkDefaultSubmitButton( event, 'CommandCreate' );" /></td>
              </tr>

              <tr>
                <td>Username:</td>
                <td><input class="textinput" type="text" name="Username" id="Username" value="<?= htmlspecialchars( PageHandler::write_post_value_if_failed( 'Username' ) ) ?>" onkeypress="xgene360_cu.checkDefaultSubmitButton( event, 'CommandCreate' );" /></td>
              </tr>

              <tr>
                <td>Password:</td>
                <td><input class="textinput" type="password" name="Password" id="Password" onkeypress="xgene360_cu.checkDefaultSubmitButton( event, 'CommandCreate' );" /></td>
              </tr>

              <tr>
                <td>Confirm Password:</td>
                <td><input class="textinput" type="password" name="ConfirmPassword" id="ConfirmPassword" onkeypress="xgene360_cu.checkDefaultSubmitButton( event, 'CommandCreate' );" /></td>
              </tr>

              <tr>
                <td colspan="2"><input type="checkbox" name="AllowGreenGene" id="AllowGreenGeneCreate" onclick="enableGreenGeneCourseSelection( this, GreenGeneCourseCreate );" /><label for="AllowGreenGeneCreate" style="font-size: 0.85em;">&nbsp;Allow user to access GreenGene site</label></td>
              </tr>

              <tr>
                <td>GreenGene Course:</td>
                <td>
                  <select name="GreenGeneCourseCreate" id="GreenGeneCourseCreate" disabled="disabled">
            
<?php
  
$res_courses = $g_obj_course_manager->view_courses();

for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $res_courses ); ++$i )
{
	$res_row = $g_obj_db->fetch( $res_courses );
	
	echo( '<option value="' . htmlspecialchars( $res_row->CourseId ) . '">&nbsp;' . htmlspecialchars( $res_row->Name ) . '&nbsp;</option>'."\n" );
}

?>
          
                  </select>&nbsp;
                </td>
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

      <br /><br />

      <table class="box">

        <tr>
          <th>Import Students</th>
        </tr>

        <tr>
          <td>
            Upload File [<a href="">Help?</a>]<br />
            <input class="fileinput" type="file" name="ImportStudentFile" id="ImportStudentFile" />
            <br />
            <input class="buttoninput" type="submit" name="Command" value="Import" onclick="return validateImportStudent();" />
          </td>
        </tr>

        <tr>
          <td><input type="checkbox" name="AllowGreenGene" id="AllowGreenGeneCourseImport" onclick="enableGreenGeneCourseSelection( this, 'GreenGeneCourseImport' );" /><label for="AllowGreenGeneCourseImport">&nbsp;Allow user to access GreenGene site</label></td>
        </tr>
              
        <tr>
          <td>GreenGene Course:
            <select name="GreenGeneCourseImport" id="GreenGeneCourseImport" disabled="disabled">

<?php
  
$res_courses = $g_obj_course_manager->view_courses();

for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $res_courses ); ++$i )
{
	$res_row = $g_obj_db->fetch( $res_courses );
    
	echo( '<option value="' . htmlspecialchars( $res_row->CourseId ) . '">&nbsp;' . htmlspecialchars( $res_row->Name ) . '&nbsp;</option>'."\n" );
}

?>

            </select>&nbsp;
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
	
	$str_user_name = PageHandler::get_post_value( 'Username' );
	$str_first_name = PageHandler::get_post_value( 'FirstName' );
	$str_last_name = PageHandler::get_post_value( 'LastName' );
	$str_password = PageHandler::get_post_value( 'Password' );
	$str_password_confirm = PageHandler::get_post_value( 'ConfirmPassword' );
	$str_student_number = PageHandler::get_post_value( 'StudentNumber' );
	$int_greengene_course = PageHandler::get_post_value( 'GreenGeneCourseCreate' );
	
	// verify the input
	if ( strlen( $str_user_name ) == 0 || strlen( $str_first_name ) == 0 || strlen( $str_last_name ) == 0 || strlen( $str_password ) == 0 || strlen( $str_student_number ) == 0 )
	{
		MessageHandler::add_message( MSG_FAIL, 'Please enter the necessary information' );
		return;
	}
	
	if ( $str_password != $str_password_confirm )
	{
		MessageHandler::add_message( MSG_FAIL, 'The password does not match' );
		return;
	}
		
	if ( empty( $int_greengene_course ) )
	{
		$int_greengene_course = 0;
	}

	
	if ( $g_obj_student_manager->create_user( $str_user_name, $int_greengene_course, UP_STUDENT,  $str_first_name, $str_last_name, $str_password, $str_student_number ) )
	{
		MessageHandler::add_message( MSG_SUCCESS, 'Successfully created an account for Student "' . $str_first_name . ' ' . $str_last_name . '"' );
	}
	
	else
	{
		MessageHandler::add_message( MSG_FAIL, 'Failed to create an account for Student "' . $str_first_name . ' ' . $str_last_name . '"' );
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
	
	$arr_student_names = $g_obj_student_manager->user_names_list( $arr_student_list );
	
	$arr_success = array();
	$arr_fail = array();
	
	for ( $i = 0; $i < count( $arr_student_names ); ++$i )
	{
		if ( $g_obj_student_manager->delete_user( $arr_student_names[$i][0] ) )
		{
			array_push( $arr_success, $arr_student_names[$i] );
		}
		
		else
		{
			array_push( $arr_fail, $arr_student_names[$i] );
		}
	}
	
	if ( count( $arr_success ) != 0 )
	{
		$str_message = PageHandler::display_users_id_name( 'Successfully deleted', $arr_success );
		
		MessageHandler::add_message( MSG_SUCCESS, $str_message );
	}
	
	if ( count( $arr_fail ) != 0 )
	{
		$str_message = PageHandler::display_users_id_name( 'Failed to delete', $arr_fail );
		
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
				array_push( $arr_success, $arr_student_names[$i] );
			}
			
			else
			{
				array_push( $arr_fail, $arr_student_names[$i] );
			}
		}
	}
	
	else
	{
		for ( $i = 0; $i < count( $arr_student_names ); ++$i )
		{
			if ( $g_obj_assign_student_manager->assign_student_to_problem( $arr_student_names[$i][0], $int_selected_problem_id ) )
			{
				array_push( $arr_success, $arr_student_names[$i] );
			}
			
			else
			{
				array_push( $arr_fail, $arr_student_names[$i] );
			}
		}
	}
	
	if ( count( $arr_success ) != 0 )
	{
		$str_message = PageHandler::display_users_id_name( 'Successfully assigned', $arr_success );
		
		MessageHandler::add_message( MSG_SUCCESS, $str_message );
	}
	
	if ( count( $arr_fail ) != 0 )
	{
		$str_message = PageHandler::display_users_id_name( 'Failed to assign', $arr_fail );
		
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
	if ( !isset( $_FILES['ImportStudentFile'] ) )
	{
		MessageHandler::add_message( MSG_FAIL, 'Please select a file' );
	}
	
	else
	{
		if ( !is_uploaded_file( $_FILES['ImportStudentFile']['tmp_name'] ) )
		{
			MessageHandler::add_message( MSG_FAIL, 'The file cannot be retrieved' );
		}
	
		else
		{
			$int_greengene_course = PageHandler::get_post_value( 'GreenGeneCourseImport' );
			
			if ( empty( $int_greengene_course ) )
			{
				$int_greengene_course = 0;
			}
			
			FileHandler::import_student_list( $_FILES['ImportStudentFile']['tmp_name'], $int_greengene_course );
		}
	}
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
