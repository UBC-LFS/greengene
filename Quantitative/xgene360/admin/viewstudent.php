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
* necessary id
*/

$g_str_parent_page = './managestudents.php';

PageHandler::check_necessary_id( array( 'StudentId' ), $g_str_parent_page );

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

/*
* required info
*/

$g_str_student_id = $_GET['StudentId'];

verify_student_exists();

process_post();

/*
* set header stuff
*/

$g_str_page_title = "View Student";
$g_arr_scripts = array( 'commonadmin.js', 'managestudents.js' );
$g_arr_nav_links = $g_arr_nav_defined_links[$g_obj_user->int_privilege];
$g_str_script_block = "xgene360_cu.using( 'sortabletable' )";

require_once( '../includes/header.inc.php' );

if ( $g_bln_display_content )
{
?>

<!-- Start Content -->
<div>

  <form id="UpdateStudentsForm" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>">

    <input class="buttonback" type="button" value="&lt;&nbsp;&nbsp;Back to Student Listing" onclick="window.location='<?= $g_str_parent_page ?>';" />
    <br /><br /><br />

    <table class="box">

      <tr>
        <th>Student Information</th>
      </tr>

      <tr>
        <td>

          <table>
			 <tr>
				<td>CWL:&nbsp;</td>
				<td><input class="longtextinput" type="text" size="30" value="<?php echo($g_arr_student_info->UserId)?>" disabled></td>
			</tr>
            <tr>
              <td>First Name:&nbsp;</td>
              <td> <input class="longtextinput" type="text" name="StudentFirstName" id="StudentFirstName" size="30" value="<?= htmlspecialchars( $g_arr_student_info->FirstName ) ?>" /></td>
            </tr>
            <tr>
              <td>Last Name:&nbsp;</td>
              <td> <input class="longtextinput" type="text" name="StudentLastName" id="StudentLastName" size="30" value="<?= htmlspecialchars( $g_arr_student_info->LastName ) ?>" /></td>
            </tr>
            <tr>
              <td colspan="2" align="right">
                <input class="buttoninput" type="submit" name="Command" value="Update" onclick="return validateUpdateStudentForm();" />&nbsp;
                <input class="buttoninput" type="reset" name="Command" value="Reset" onclick="return resetUpdateStudentForm();" />
              </td>
            </tr>
          </table>

        </td>
      </tr>

    </table>

    <br />

    <table class="format" width="100%">

      <tr>
        <td>
          <strong>The student has access to the following problems:</strong>
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
                <input class="buttoninput" type="submit" name="Command" value="Assign" onclick="return validateAssignStudentToACourseOrAProblem();" />
              </td>
            </tr>

          </table>

        </td>
        
      </tr>

    </table>

    <table class="listing" border="0" cellpadding="0" cellspacing="0" id="ListOfProblems">
      <tr>
        <th><input type="checkbox" id="ProblemIdSelectionToggle" onclick="xgene360_cu.checkAll( this, 'ProblemId[]' );" /></th>
        <th>Problem</th>
        <th>Course</th>
        <th>Start Date</th>
        <th>Due Date</th>
        <th>Progress</th>
      </tr>
    
<?php

$res_problems = $g_obj_student_manager->view_student_problems( $g_str_student_id );

if ( $g_obj_db->get_number_of_rows( $res_problems ) == 0 )
{
	echo( '<tr>' . "\n" );
	echo( '<td colspan="6">This student does not have any problem assigned</td>' . "\n" );
	echo( '</tr>' . "\n" );
}

for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $res_problems ); ++$i )
{
	$res_row = $g_obj_db->fetch( $res_problems );

	echo( '<tr onclick="openProblemDetail( \'' . htmlspecialchars( $res_row->problem_id, ENT_QUOTES ) . '\' );" onmouseover="xgene360_cu.useHandCursor( this );" onmouseout="xgene360_cu.useRegularCursor( this );">' . "\n" );
	echo( '<td onmouseover="xgene360_cu.stopPropagation( event );" onclick="xgene360_cu.stopPropagation( event );"><input type="checkbox" name="ProblemId[]" value="' . htmlspecialchars( $res_row->problem_id ) . '" /></td>' . "\n" );
	echo( '<td>' . htmlspecialchars( $res_row->problem_name ). '</td>' ."\n" );
	echo( '<td>' . htmlspecialchars( $res_row->Name ) . '</td>' . "\n" );
	echo( '<td>' . htmlspecialchars( PageHandler::format_date( $res_row->start_date ) ) . '</td>' . "\n" );
	echo( '<td>' . htmlspecialchars( PageHandler::format_date( $res_row->due_date ) ) . '</td>' . "\n" );
	echo( '<td><input class="buttoninput" type="button" value="View Progress" onclick="openProgress( event, ' . $res_row->problem_id . ', \'' . $g_str_student_id . '\' );" /></td>' . "\n" );
	echo( '</tr>' . "\n" );
}
	
?>
    
    </table>

    <input class="buttoninput" type="submit" value="Drop Selected Problems" name="Command" onclick="return validateDropProblem();" />

    <br /><br /><br /><br />    

    <input class="buttonback" type="button" value="&lt;&nbsp;&nbsp;Back to Student Listing" onclick="window.location='<?= $g_str_parent_page ?>';" />
    <input type="hidden" name="SerialId" id="SerialId" value="<?= $g_str_serial ?>"/>
  </form>

</div>
<!-- End Content -->

<?php
}

require_once('../includes/footer.inc.php');

$g_obj_db->disconnect();

/**  Function: void verify_student_exists()
*    ---------------------------------------------------------------- 
*    Purpose:           Verify the student specified by StudentId
*                       exists
*    Arguments:         None
*                       
*    Returns/Assigns:   Set the student information to
*                       $g_arr_student_info if the student exists
*/
function verify_student_exists()
{
	global $g_obj_student_manager, $g_obj_db, $g_str_student_id, $g_arr_student_info;
	
	$res_student = $g_obj_student_manager->view_user( $g_str_student_id );
	
	if ( $g_obj_db->get_number_of_rows( $res_student ) == 0 )
	{
		MessageHandler::add_message( MSG_ERROR, 'The Student does not exist' );
	}
	
	else
	{
		$g_arr_student_info = $g_obj_db->fetch( $res_student );
	}
}

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
			case 'Update':
			{
				on_update_handler();
			}
			break;
			
			case 'Assign':
			{
				on_assign_handler();
			}
			break;
			
			case 'Drop Selected Problems':
			{
				on_remove_handler();
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

/**  Function: void on_update_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process updating the student
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_update_handler()
{
	global $g_obj_student_manager, $g_str_student_id;
	
	$str_first_name = PageHandler::get_post_value( 'StudentFirstName' );
	$str_last_name = PageHandler::get_post_value( 'StudentLastName' );
	
	if ( $g_obj_student_manager->modify_user( $g_str_student_id, $str_first_name, $str_last_name) )
	{
		MessageHandler::add_message( MSG_SUCCESS, 'Successfully updated the account for Student "' . $str_first_name . ' ' . $str_last_name . '"' );
	}
	
	else
	{
		MessageHandler::add_message( MSG_FAIL, 'Failed to update the account for Student "' . $str_first_name . ' ' . $str_last_name . '"' );
	}
	
	// force to load the updated info
	verify_student_exists();
}

/**  Function: void on_assign_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process assigning the student to the selected
*                       course or problem
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_assign_handler()
{
	global $g_obj_assign_student_manager, $g_str_student_id;
	
	$int_selected_course_id = PageHandler::get_post_value( 'SelectedCourse' );
	$int_selected_problem_id = PageHandler::get_post_value( 'SelectedProblem' );
	
	if ( strlen( $int_selected_course_id == 0 && $int_selected_problem_id == 0 ) )
	{
		MessageHandler::add_message( MSG_FAIL, "Please select a course or a problem" );
		return;
	}
	
	if ( strlen( $int_selected_course_id ) != 0 )
	{
		if ( $g_obj_assign_student_manager->assign_student_to_course( $g_str_student_id, $int_selected_course_id ) )
		{
			MessageHandler::add_message( MSG_SUCCESS, 'Successfully assigned the Student to the Course' );
		}
		
		else
		{
			MessageHandler::add_message( MSG_FAIL, 'Failed to assign the Student to the Course' );
		}
	}
	
	else
	{
		if ( $g_obj_assign_student_manager->assign_student_to_problem( $g_str_student_id, $int_selected_problem_id ) )
		{
			MessageHandler::add_message( MSG_SUCCESS, 'Successfully assigned the Student to the Problem' );
		}
		
		else
		{
			MessageHandler::add_message( MSG_FAIL, 'Failed to assign the Student to the Problem' );
		}
	}
}

/**  Function: void on_remove_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process removing the student from selected
*                       problems
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_remove_handler()
{
	global $g_obj_assign_student_manager, $g_str_student_id;
	
	$arr_problem_list = PageHandler::get_post_value( 'ProblemId' );	
	
	if ( $arr_problem_list == null )
	{
		MessageHandler::add_message( MSG_FAIL, "Please select at least one problem" );
		return;
	}
	
	$arr_success = array();
	$arr_fail = array();
	
	foreach( $arr_problem_list as $int_problem_id )
	{
		if ( $g_obj_assign_student_manager->unassign_student_from_problem( $g_str_student_id, $int_problem_id ) )
		{
			array_push( $arr_success, $int_problem_id );
		}
		
		else
		{
			array_push( $arr_fail, $int_problem_id );
		}
	}
	
	if ( count( $arr_success ) != 0 )
	{
		MessageHandler::add_message( MSG_SUCCESS, 'Successfully removed the Student  from ' . count( $arr_success ) . ' problem(s)' );
	}
	
	if ( count( $arr_fail ) != 0 )
	{
		MessageHandler::add_message( MSG_FAIL, 'Failed to remove the Student from ' . count( $arr_fail ) . ' problem(s)' );
	}
}

?>
