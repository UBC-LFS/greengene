<?php

/*
* include necessary files
*/

require_once( '../includes/global.inc.php' );
require_once( '../includes/classes/db/tamanager.class.php' );
require_once( '../includes/classes/db/coursemanager.class.php' );
require_once( '../includes/classes/db/assigntamanager.class.php' );

/*
* initialize common stuff
*/

$g_obj_db = null;
$g_obj_lock = null;
$g_str_serial = null;
$g_obj_user = null;

// PageHandler::initialize();
// PageHandler::check_permission( array( UP_ADMINISTRATOR, UP_PROFESSOR, UP_TA ) );

$pageHandler = (new PageHandler);
(new PageHandler) -> initialize();
(new PageHandler) -> check_permission( array( UP_ADMINISTRATOR, UP_PROFESSOR, UP_TA ) );

$g_obj_ta_manager = new TAManager( $g_obj_user, $g_obj_db );
$g_obj_course_manager = new CourseManager( $g_obj_user, $g_obj_db );
$g_obj_assign_ta_manager = new AssignTAManager( $g_obj_user, $g_obj_db );

process_post();

/*
* set header stuff
*/

$g_str_page_title = "Manage TAs";
$g_arr_scripts = array( 'managetas.js', 'commonadmin.js' );
$g_arr_nav_links = $g_arr_nav_defined_links[$g_obj_user->int_privilege];
$g_str_script_block = "xgene360_cu.using( 'sortabletable' )";

require_once( '../includes/header.inc.php' );

if ( $g_bln_display_content )
{
?>

<!-- Start Content -->
<div>
  <form id="ManageTAsForm" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" enctype="multipart/form-data">

    <strong>
      Click on a TA to edit specific TA information. 
      <br />To delete a TA or several TAs, click on the checkbox beside each TA to be deleted and press 'Delete Selected'. 
      <br />You can also import or export lists of TAs.    
    </strong>    
    
    <br /><br />

    <table class="format" width="100%">
      <tr>
        <td>
          <input class="buttoninput" type="button" value="Add New" name="Command" onclick="displayCreateTA();" />&nbsp;
          <input class="buttoninput" type="button" value="Import List" name="Command" onclick="displayImportTA();" />&nbsp;
          <input class="buttoninput" type="submit" value="Export Selected" name="Command" onclick="return validateTASelection();" />&nbsp;
          <input class="buttoninput" type="submit" value="Delete Selected" name="Command" onclick="return validateDeleteTA();" />
        </td>
        <td align="right">
           <select size="1" name="SelectedCourse" id="SelectedCourse">
              
<?php
  
echo( '<option selected="selected" value="">&nbsp;Assign selected to course:&nbsp;&nbsp;&nbsp;&nbsp;</option>' );
echo( '<option value="">&nbsp;----------</option>' );
echo( '<option value="">&nbsp;</option>' );

$res_courses = $g_obj_course_manager->view_courses();


if ($res_courses != NULL) { // added condition - prevents error if no courses available (added during PHP8 migration)
	for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $res_courses ); ++$i )
	{
		$res_row = $g_obj_db->fetch( $res_courses );

		echo( '<option value="' . htmlspecialchars( $res_row->CourseId ) . '">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . htmlspecialchars( $res_row->Name ) . '</option>'."\n" );
	}
}
?>

           </select>
           &nbsp;
           <input align="right" class="buttoninput" type="submit" value="Assign" name="Command" onclick="return validateAssignTAsToACourse();"/>
        </td>
      </tr>
    </table>

    <table class="listing" id="ListOfTAs">
      <tr>
        <th width="50"><input type="checkbox" id="UserIdSelectionToggle" onclick="xgene360_cu.checkAll( this, 'TAId[]' );" /></th>
        <th width="150">CWL Username</th>
        <th width="150">First Name</th>
        <th >Last Name</th>
      </tr>
		
<?php

$res_tas = $g_obj_ta_manager->view_tas();

if ($res_tas != NULL) { // added condition - prevents error if no courses available (added during PHP8 migration)
	for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $res_tas ); ++$i )
	{
		$res_row = $g_obj_db->fetch( $res_tas );

		echo( '<tr onclick="openTADetail( \'' . htmlspecialchars( $res_row->UserId ) . '\' );" onmouseover="hightlightSelectedRow( this, true );" onmouseout="hightlightSelectedRow( this, false );">' . "\n" );
		echo( '<td onmouseover="xgene360_cu.stopPropagation( event );" onclick="xgene360_cu.stopPropagation( event );"><input type="checkbox" name="TAId[]" value="' . htmlspecialchars( $res_row->UserId ) . '" /></td>' . "\n" );
		echo( '<td>' . htmlspecialchars( $res_row->UserId ) . '</td>' );
		echo( '<td>' . htmlspecialchars( $res_row->FirstName ) . '</td>' );
		echo( '<td>' . htmlspecialchars( $res_row->LastName ) . '</td>' );
		echo( '</tr>' );
	}
}
?>

    </table>

    <input class="buttoninput" type="button" value="Add New" name="Command" onclick="displayCreateTA();" />&nbsp;
    <input class="buttoninput" type="button" value="Import List" name="Command" onclick="displayImportTA();" />&nbsp;
    <input class="buttoninput" type="submit" value="Export Selected" name="Command" onclick="return validateTASelection();" />&nbsp;
    <input class="buttoninput" type="submit" value="Delete Selected" name="Command" onclick="return validateDeleteTA();" />

    <div id="CreateTADiv" style="display: none">
        
      <br /><br />    
      <table class="box">
        <tr>
          <th>Create New TA</th>
        </tr>
        <tr>
          <td>

            <table>
              <tr>
                <td width="125">First Name:</td>
                <td><input class="textinput" type="text" name="FirstName" id="FirstName" value="<?= htmlspecialchars( (new PageHandler) -> write_post_value_if_failed( 'FirstName' ) ) ?>" onkeypress="xgene360_cu.checkDefaultSubmitButton( event, 'CommandCreate' );" /></td>
              </tr>
              <tr>
                <td>Last Name:</td>
                <td><input class="textinput" type="text" name="LastName" id="LastName" value="<?= htmlspecialchars( (new PageHandler) -> write_post_value_if_failed( 'LastName' ) ) ?>" onkeypress="xgene360_cu.checkDefaultSubmitButton( event, 'CommandCreate' );" /></td>
              </tr>
              <tr>
                <td>CWL Username:</td>
                <td><input class="textinput" type="text" name="Username" id="Username" value="<?= htmlspecialchars( (new PageHandler) -> write_post_value_if_failed( 'Username' ) ) ?>" onkeypress="xgene360_cu.checkDefaultSubmitButton( event, 'CommandCreate' );" /></td>
              </tr>
              <tr>
                <td colspan="2" align="right">
                <input class="buttoninput" type="submit" name="Command" id="CommandCreate" value="Create" onclick="return validateCreateTAForm();" />
                &nbsp;<input class="buttoninput" type="reset" name="Reset" value="Reset" onclick="return resetCreateTAForm();" />
                </td>
              </tr>
            </table>

          </td>
        </tr>
      </table>

    </div>
    
    <div id="ImportTADiv" style="display: none">

      <br /><br />

      <table class="box">

        <tr>
          <th>Import TAs</th>
        </tr>

        <tr>
          <td>
            Upload File [<a href="">Help?</a>]<br />
            <input class="fileinput" type="file" name="ImportTAFile" id="ImportTAFile" />
            <br />
            <input class="buttoninput" type="submit" name="Command" value="Import" onclick="return validateImportTA();" />
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
				(new MessageHandler) ->  add_message( MSG_ERROR, "Unknown Command" );
			}
			break;
		}
	}
}

/**  Function: void on_create_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process creating the TA
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_create_handler()
{
	global $g_obj_ta_manager;
	
	$str_user_name = (new PageHandler) -> get_post_value( 'Username' );
	$str_first_name = (new PageHandler) -> get_post_value( 'FirstName' );
	$str_last_name = (new PageHandler) -> get_post_value( 'LastName' );

	// verify the input
	if ( !isset($str_user_name))
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, 'Please enter a valid CWL Username' );
		return;
	}
	
	$str_first_name = !isset($str_first_name) ? " " : $str_first_name;
	$str_last_name = !isset($str_last_name) ? " " : $str_last_name;
	
	// create a new ta
	if ( $g_obj_ta_manager->create_user( $str_user_name, UP_TA,  $str_first_name, $str_last_name ) )
	{
		(new MessageHandler) ->  add_message( MSG_SUCCESS, 'Successfully created an account for TA "' . $str_user_name  );
	}
	
	else
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, 'Failed to create an account for TA "' . $str_user_name . '"' );
	}
}

/**  Function: void on_delete_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process deleting the TAs
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_delete_handler()
{
	global $g_obj_ta_manager;
	
	$arr_ta_list = (new PageHandler) -> get_post_value( 'TAId' );
	
	if ( $arr_ta_list == null )
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, "Please select at least one TA" );
		return;
	}
	
	$arr_ta_names = $g_obj_ta_manager->user_names_list( $arr_ta_list );
	
	$arr_success = array();
	$arr_fail = array();
	
	for ( $i = 0; $i < count( $arr_ta_names ); ++$i )
	{
		if( $g_obj_ta_manager->delete_user( $arr_ta_names[$i][0] ) )
		{
			array_push( $arr_success, $arr_ta_names[$i][0] );
		}
		
		else
		{
			array_push( $arr_fail, $arr_ta_names[$i][0] );
		}
	}
	
	if ( count( $arr_success ) != 0 )
	{
		$str_message = (new PageHandler) -> display_users_cwl( 'Successfully deleted', $arr_success );
		
		(new MessageHandler) ->  add_message( MSG_SUCCESS, $str_message );
	}
	
	if ( count( $arr_fail ) != 0 )
	{
		$str_message = (new PageHandler) -> display_users_cwl( 'Failed to delete', $arr_fail );
		
		(new MessageHandler) ->  add_message( MSG_FAIL, $str_message );
	}
}

/**  Function: void on_assign_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process assigning the TAs
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_assign_handler()
{
	global $g_obj_ta_manager, $g_obj_assign_ta_manager;
	
	$arr_ta_list = (new PageHandler) -> get_post_value( 'TAId' );	
	$int_selected_course_id = (new PageHandler) -> get_post_value( 'SelectedCourse' );
	
	if ( $arr_ta_list == null || strlen( $int_selected_course_id ) == 0 )
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, "Please select at least one TA and select a course" );
		return;
	}
	
	$arr_ta_names = $g_obj_ta_manager->user_names_list( $arr_ta_list );
	
	$arr_success = array();
	$arr_fail = array();
	
	for ( $i = 0; $i < count( $arr_ta_names ); ++$i )
	{
		if ( $g_obj_assign_ta_manager->assign_TA( $arr_ta_names[$i][0], $int_selected_course_id ) )
		{
			array_push( $arr_success, $arr_ta_names[$i][0]);
		}
		
		else
		{
			array_push( $arr_fail, $arr_ta_names[$i][0]);
		}
	}
	
	if ( count( $arr_success ) != 0 )
	{
		$str_message = (new PageHandler) -> display_users_cwl( 'Successfully assigned', $arr_success );
		
		(new MessageHandler) ->  add_message( MSG_SUCCESS, $str_message );
	}
	
	if ( count( $arr_fail ) != 0 )
	{
		$str_message = (new PageHandler) -> display_users_cwl( 'Failed to assign', $arr_fail );
		
		(new MessageHandler) ->  add_message( MSG_FAIL, $str_message );
	}
}

/**  Function: void on_import_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process importing the TAs list
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_import_handler()
{
	if ( !isset( $_FILES['ImportTAFile'] ) )
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, 'Please select a file' );
	}
	
	else
	{
		if ( !is_uploaded_file( $_FILES['ImportTAFile']['tmp_name'] ) )
		{
			(new MessageHandler) ->  add_message( MSG_FAIL, 'The file cannot be retrieved' );
		}
		
		else
		{
			(new FileHandler) -> import_ta_list( $_FILES['ImportTAFile']['tmp_name']);
		}
	}
}

/**  Function: void on_export_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process exporting the TAs list
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_export_handler()
{
	$arr_ta_list = (new PageHandler) -> get_post_value( 'TAId' );
	
	if ( $arr_ta_list == null )
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, "Please select at least one TA" );
		return;
	}
	
	else
	{
		(new FileHandler) -> export_ta_list( $arr_ta_list );
	}
}

?>
