<?php

/*
* include necessary files
*/

require_once( '../includes/global.inc.php' );
require_once( '../includes/classes/db/professormanager.class.php' );
require_once( '../includes/classes/db/coursemanager.class.php' );
require_once( '../includes/classes/db/assignprofessormanager.class.php' );

/*
* necessary id
*/

$g_str_parent_page = './manageprofessors.php';

(new PageHandler) -> check_necessary_id( array( 'ProfessorId' ), $g_str_parent_page );

/*
* initialize common stuff
*/

$g_obj_db = null;
$g_obj_lock = null;
$g_str_serial = null;
$g_obj_user = null;

// PageHandler::initialize();
// PageHandler::check_permission( array( UP_ADMINISTRATOR, UP_PROFESSOR ) );

$pageHandler = (new PageHandler);
(new PageHandler) -> initialize();
(new PageHandler) -> check_permission( array( UP_ADMINISTRATOR, UP_PROFESSOR ) );

$g_obj_professor_manager = new ProfessorManager( $g_obj_user, $g_obj_db );
$g_obj_course_manager = new CourseManager( $g_obj_user, $g_obj_db );
$g_obj_assign_professor_manager = new AssignProfessorManager( $g_obj_user, $g_obj_db );

/*
* required info
*/

$g_str_professor_id = $_GET["ProfessorId"];

verify_professor_exists();

process_post();

/*
* set header stuff
*/

$g_str_page_title = "View Professor";
$g_arr_scripts = array( 'manageprofessors.js', 'commonadmin.js' );
$g_arr_nav_links = $g_arr_nav_defined_links[$g_obj_user->int_privilege];
$g_str_script_block = "xgene360_cu.using( 'sortabletable' )";

require_once( '../includes/header.inc.php' );

if ( $g_bln_display_content )
{
?>

<!-- Start Content -->
<div>

  <form method="post" id="UpdateProfessorForm" action="<?= $_SERVER['REQUEST_URI'] ?>">

    <input class="buttonback" type="button" value="&lt;&nbsp;&nbsp;Back to Professor Listing" onclick="window.location='<?= $g_str_parent_page ?>';" />
    <br /><br /><br />

<?php

if ( $g_obj_user->int_privilege == UP_ADMINISTRATOR || $g_obj_user->str_username == $g_str_professor_id )
{
?>

    <table class="box">

      <tr>
        <th>Professor Information</th>
      </tr>

      <tr>
        <td>

          <table>

            <tr>
              <td width="150">First Name:</td>
              <td><input class="longtextinput" type="text" name="ProfessorFirstName" value="<?= htmlspecialchars( $g_arr_professor_info->FirstName ) ?>" /></td>
            </tr>

            <tr>
              <td>Last Name:</td>
              <td><input class="longtextinput" type="text" name="ProfessorLastName" value="<?= htmlspecialchars( $g_arr_professor_info->LastName ) ?>"/></td>
			</tr>

            <tr>
              <td colspan="2" align="right">
                <input class="buttoninput" type="submit" name="Command" value="Update" onclick="return validateUpdateProfessorForm();" />&nbsp;
                <input class="buttoninput" type="reset" name="Command" value="Reset" onclick="return resetUpdateProfessorForm();" />
              </td>
            </tr>

          </table>

        </td>
      </tr>

    </table>

    <br /><br />

<?php
}
?>

    <table class="format" width="100%">

      <tr>
        <td>
          <strong>The professor has access to the following courses:</strong>
        </td>

        <td align="right">

          <select name="SelectedCourse" id="SelectedCourse">
        
<?php

	echo( '<option selected="selected" value="">&nbsp;Assign professor to course:&nbsp;&nbsp;</option>' );         
	echo( '<option value="">&nbsp;----------</option>' );   
	echo( '<option value="">&nbsp;</option>' );

	$res_courses = $g_obj_course_manager->view_courses();
	
	for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $res_courses ); ++$i )
	{
		$res_row = $g_obj_db->fetch( $res_courses );
		
		echo( '<option value="' . htmlspecialchars( $res_row->CourseId ) . '">&nbsp;' . htmlspecialchars( $res_row->Name ) . '&nbsp;</option>' . "\n" );
	}
	
?>
         
          </select>

          &nbsp;<input class="buttoninput" type="submit" value="Assign" name="Command" onclick="return onAssignAProfessorToACourseButtonClickHandler( 'SelectCourse' );" />

        </td>
      </tr>

    </table>

    <table class="listing" id="listofCourses">

      <tr>
        <th width="50"><input type="checkbox" id="CourseIdSelectionToggle" onclick="xgene360_cu.checkAll( this, 'CourseId[]' );" /></th>
        <th width="150">Course Name</th>
        <th>Course Description</th>
      </tr>
      
<?php

	$res_professor_courses = $g_obj_professor_manager->view_professor_courses( $g_str_professor_id );
	
	if ( $g_obj_db->get_number_of_rows( $res_professor_courses ) == 0 )
	{
		echo( '<tr>'."\n" );
		echo( '<td colspan="3">This professor is not assigned to any course</td>'."\n" );
		echo( '</tr>'."\n" );
	}

	for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $res_professor_courses ); ++$i )
	{
		$res_row = $g_obj_db->fetch( $res_professor_courses );
	  
		echo( '<tr onclick="openCourseDetail( \'' . htmlspecialchars( $res_row->CourseId, ENT_QUOTES ) . '\' );" onmouseover="xgene360_cu.useHandCursor( this );" onmouseout="xgene360_cu.useRegularCursor( this );">' . "\n" );
		echo( '<td onmouseover="xgene360_cu.stopPropagation( event );" onclick="xgene360_cu.stopPropagation( event );"><input type="checkbox" name="CourseId[]" value="' . htmlspecialchars( $res_row->CourseId ) . '" /></td>' . "\n" );
		echo( '<td>' . htmlspecialchars( $res_row->Name ) . '</td>' . "\n" );
		echo( '<td>' . htmlspecialchars( $res_row->Description ) . '</td>' . "\n" );
		echo( '</tr>' . "\n" );
	}
  
?>

    </table>

    <input class="buttoninput" type="submit" value="Drop Selected Courses" name="Command" onclick="return validateDropProfessorFromCourses();" />

    <br /><br /><br />

    <input class="buttonback" type="button" value="&lt;&nbsp;&nbsp;Back to Professor Listing" onclick="window.location='<?= $g_str_parent_page ?>';" />
    <input type="hidden" name="SerialId" id="SerialId" value="<?= $g_str_serial ?>"/>

  </form>

</div>
<!-- End Content -->

<?php
}

require_once('../includes/footer.inc.php'); 

$g_obj_db->disconnect();

/**  Function: void verify_professor_exists()
*    ---------------------------------------------------------------- 
*    Purpose:           Verify the professor specified by ProfessorId
*                       exists
*    Arguments:         None
*                       
*    Returns/Assigns:   Set the professor information to
*                       $g_arr_professor_info if the professor exists
*/
function verify_professor_exists()
{
	global $g_obj_professor_manager, $g_obj_db, $g_str_professor_id, $g_arr_professor_info;
	
	$res_professor = $g_obj_professor_manager->view_user( $g_str_professor_id );
	
	if ( $g_obj_db->get_number_of_rows( $res_professor ) == 0 )
	{
		(new MessageHandler) ->  add_message( MSG_ERROR, 'The Professor does not exist' );
	}
	
	else
	{
		$g_arr_professor_info = $g_obj_db->fetch( $res_professor );
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
	
	if ( isset( $_POST['Command'] ) && $g_obj_lock->page_lock( (new PageHandler) -> get_post_value( 'SerialId' ) ) )
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
			
			case 'Drop Selected Courses':
			{
				on_remove_handler();
			}
			break;
			
			default:
			{
				(new MessageHandler) ->  add_message( MSG_ERROR, "Unknown Command" );
			}
			break;
		}
	}
}

/**  Function: void on_update_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process updating the professor
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_update_handler()
{
	global $g_obj_professor_manager, $g_str_professor_id;
	
	$str_first_name = (new PageHandler) -> get_post_value( 'ProfessorFirstName' );
	$str_last_name = (new PageHandler) -> get_post_value( 'ProfessorLastName' );
	
	if ( strlen( $str_first_name ) == 0 || strlen( $str_last_name ) == 0 )
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, 'Please enter the necessary information' );
		return;
	}
	
	if ( $g_obj_professor_manager->modify_user( $g_str_professor_id, $str_first_name, $str_last_name) )
	{
		(new MessageHandler) ->  add_message( MSG_SUCCESS, 'Successfully updated the account for Professor "' . $str_first_name . ' ' . $str_last_name . '"' );
	}
	
	else
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, 'Failed to update the account for Professor "' . $str_first_name . ' ' . $str_last_name . '"' );
	}
	
	// force to load updated info
	verify_professor_exists();
}

/**  Function: void on_assign_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process assigning the professor to selected
*                       course
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_assign_handler()
{
	global $g_obj_assign_professor_manager, $g_str_professor_id;
	
	$int_selected_course_id = (new PageHandler) -> get_post_value( 'SelectedCourse' );
	
	if ( strlen( $int_selected_course_id ) == 0 )
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, "Please select a course" );
		return;
	}
	
	if ( $g_obj_assign_professor_manager->assign_professor( $g_str_professor_id, $int_selected_course_id ) )
	{
		(new MessageHandler) ->  add_message( MSG_SUCCESS, 'Successfully assigned the Professor to the Course' );
	}
	
	else
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, 'Failed to assign the Professor to the Course' );
	}
}

/**  Function: void on_remove_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process removing the professor from selected
*                       courses
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_remove_handler()
{
	global $g_obj_assign_professor_manager, $g_str_professor_id;
	
	$arr_course_list = (new PageHandler) -> get_post_value( 'CourseId' );	
	
	if ( $arr_course_list == null )
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, "Please select at least one professor" );
		return;
	}
	
	$arr_success = array();
	$arr_fail = array();
	
	foreach( $arr_course_list as $int_course_id )
	{
		if ( $g_obj_assign_professor_manager->unassign_professor( $g_str_professor_id, $int_course_id ) )
		{
			array_push( $arr_success, $int_course_id );
		}
		
		else
		{
			array_push( $arr_fail, $int_course_id );
		}
	}
	
	if ( count( $arr_success ) != 0 )
	{
		(new MessageHandler) ->  add_message( MSG_SUCCESS, 'Successfully removed the Professor from ' . count( $arr_success ) . ' course(s)' );
	}
	
	if ( count( $arr_fail ) != 0 )
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, 'Failed to remove the Professor from ' . count( $arr_fail ) . ' course(s)' );
	}
}

?>
