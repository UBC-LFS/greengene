<?php

/*
* include necessary files
*/

require_once( '../includes/global.inc.php' );
require_once( '../includes/classes/db/coursemanager.class.php' );

/*
* initialize common stuff
*/

$g_obj_db = null;
$g_obj_lock = null;
$g_str_serial = null;
$g_obj_user = null;

$pageHandler = (new PageHandler);
(new PageHandler) -> initialize();
(new PageHandler) -> check_permission( array( UP_ADMINISTRATOR, UP_PROFESSOR, UP_TA ) );

$g_obj_course_manager = new CourseManager( $g_obj_user, $g_obj_db );

process_post();

/*
* set header stuff
*/

$g_str_page_title = "Manage Courses";
$g_arr_scripts = array( 'managecourses.js', 'commonadmin.js' );
$g_arr_nav_links = $g_arr_nav_defined_links[$g_obj_user->int_privilege];
$g_str_script_block = "xgene360_cu.using( 'sortabletable' )";

require_once( '../includes/header.inc.php' );

if ( $g_bln_display_content )
{
?>

<!-- Start Content -->

<div>

  <form id="ManageCoursesForm" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>">

    <strong>
      Click on a course to edit specific course information. 
      <br />To delete a course or several course, click on the checkbox beside each course to be deleted and press 'Delete Selected Courses'.
    </strong>
    <br /><br />
<?php

if ( $g_obj_user->int_privilege != UP_TA )
{
?>

    <input class="buttoninput" type="button" value="Create New Course" name="Command" onclick="displayCreateCourse();" />&nbsp;
    <input class="buttoninput" type="submit" value="Delete Selected Courses" name="Command" onclick="return validateDeleteCourse();" />

<?php
}
?>

    <table class="listing" id="ListOfCourses">

      <tr>
        <th width="50"><input type="checkbox" id="CourseIdSelectionToggle" onclick="xgene360_cu.checkAll( this, 'CourseId[]' );" /></th>
        <th width="150">Course Name</th>
        <th>Description</th>
      </tr>
      
<?php

$res_courses = $g_obj_course_manager->view_courses();

if ($res_courses != NULL) { // added condition - prevents error if no courses available (added during PHP8 migration)
	for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $res_courses ); ++$i )
	{
		$res_row = $g_obj_db->fetch( $res_courses );
		
		echo( '<tr onclick="openCourseDetail( \'' . htmlspecialchars( $res_row->CourseId, ENT_QUOTES ) . '\' );" onmouseover="hightlightSelectedRow( this, true );" onmouseout="hightlightSelectedRow( this, false );">' . "\n" );
		echo( '<td onmouseover="xgene360_cu.stopPropagation( event );" onclick="xgene360_cu.stopPropagation( event );"><input type="checkbox" name="CourseId[]" value="' . htmlspecialchars( $res_row->CourseId ) . '" /></td>' . "\n" );
		echo( '<td>' . htmlspecialchars( $res_row->Name ) . '</td>' . "\n" );
		echo( '<td>' . htmlspecialchars( $res_row->Description ) .'</td>' . "\n" );
		echo( '</tr>' . "\n" );
	}
}
    
?>
      
    </table>

<?php

if ( $g_obj_user->int_privilege != UP_TA )
{
?>              

    <table class="format" width="100%">
      <tr>
        <td>
          <input class="buttoninput" type="button" value="Create New Course" name="Command" onclick="displayCreateCourse();" />&nbsp;
          <input class="buttoninput" type="submit" value="Delete Selected Courses" name="Command" onclick="return validateDeleteCourse();" />
        </td>
      </tr>
    </table>

<?php
}
?>    
    <div id="CreateCourseDiv" style="display: none">

      <br /><br />

      <table class="box">

        <tr>
          <th>Create Course</th>
        </tr>
        
        <tr>
          <td>    
              
            <table>
            
              <tr>
                <td width="75">Name:</td>
                <td><input class="textinput" type="text" name="CourseName" id="CourseName" value="<?= htmlspecialchars( (new PageHandler) -> write_post_value_if_failed( 'CourseName' ) ) ?>" onkeypress="xgene360_cu.checkDefaultSubmitButton( event, 'CommandCreate' );" /></td>
              </tr>
              
              <tr>
                <td style="vertical-align: top">Description:</td>
                <td>
                  <textarea class="textareainput" name="CourseDescription" id="CourseDescription" cols="60" rows="10" onkeydown="xgene360_cu.countText( 'CourseDescription', 'CourseDescriptionLetterCount', 250 );" onkeyup="xgene360_cu.countText( 'CourseDescription', 'CourseDescriptionLetterCount', 250 );"><?= htmlspecialchars( (new PageHandler) -> write_post_value_if_failed( 'Username' ) ) ?></textarea><br />
                  <input readonly="readonly" class="smallnumberinput" type="text" name="CourseDescriptionLetterCount" id="CourseDescriptionLetterCount" size="3" value="<?= 250 - strlen( (new PageHandler) -> write_post_value_if_failed( 'CourseName' ) ) ?>" />&nbsp;characters left
                </td>
              </tr>

              <tr>
                <td colspan="2" align="right">
                  <input class="buttoninput" type="submit" name="Command" id="CommandCreate" value="Create" onclick="return validateCreateCourse();" />
                  &nbsp;<input class="buttoninput" type="reset" name="Reset" value="Reset" onclick="return confirmResetCreateCourse();" />
                </td>
              </tr>
            </table>
            
          </td>
        </tr>
      
      </table>
  
    </div>
    
    <input type="hidden" name="SerialId" id="SerialId" value="<?= $g_str_serial ?>" />
 
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
	
	if ( isset( $_POST['Command'] ) && $g_obj_lock->page_lock( (new PageHandler) -> get_post_value( 'SerialId' ) ) )
	{ 
		$str_command = $_POST['Command'];

		switch( $str_command )
		{ 
			case 'Create':
			{
				on_create_handler();
			}
			break;

			case 'Delete Selected Courses':
			{
				on_delete_handler();
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

/**  Function: void on_create_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process creating the course
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_create_handler()
{
	global $g_obj_course_manager;
	
	// create a new course
	$str_course_name = (new PageHandler) -> get_post_value( 'CourseName' );
	$str_course_description = (new PageHandler) -> get_post_value( 'CourseDescription' );
	
	var_dump($str_course_name, $str_course_description);


	// verify the input
	if ( !isset( $str_course_name ) || !isset( $str_course_description ) )
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, 'Please enter the necessary information' );
		return;
	}
	 
	// process the input
	if ( $g_obj_course_manager->add_course( $str_course_name, $str_course_description ) )
	{
		(new MessageHandler) ->  add_message( MSG_SUCCESS, 'Successfully created Course "' . $str_course_name . '"' );
	}
	
	else
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, 'Failed to create Course "' . $str_course_name . '"' );
	}
}

/**  Function: void on_delete_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process deleting the courses
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_delete_handler()
{
	global $g_obj_course_manager;
	
	$arr_course_list = (new PageHandler) -> get_post_value( 'CourseId' );
	
	if ( $arr_course_list == null )
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, "Please select at least one course" );
		return;	
	}
	
	$arr_success = array();
	$arr_fail = array();
	
	foreach( $arr_course_list as $int_course_id )
	{
		if ( $g_obj_course_manager->delete_course( $int_course_id ) )
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
		(new MessageHandler) ->  add_message( MSG_SUCCESS, 'Successfully deleted ' . count( $arr_success ) . ' course(s)' );
	}
	
	if ( count( $arr_fail ) != 0 )
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, 'Failed to delete ' . count( $arr_fail ) . ' course(s)' );
	}
}

?>
