<?php

/*
* include necessary files
*/

require_once( '../includes/global.inc.php' );
require_once( '../includes/classes/db/professormanager.class.php' );
require_once( '../includes/classes/db/coursemanager.class.php' );
require_once( '../includes/classes/db/assignprofessormanager.class.php' );

/*
* initialize common stuff
*/

$g_obj_db = null;
$g_obj_lock = null;
$g_str_serial = null;
$g_obj_user = null;

PageHandler::initialize();
PageHandler::check_permission( array( UP_ADMINISTRATOR, UP_PROFESSOR, UP_TA ) );

$g_obj_professor_manager = new ProfessorManager( $g_obj_user, $g_obj_db );
$g_obj_course_manager = new CourseManager( $g_obj_user, $g_obj_db );
$g_obj_assign_professor_manager = new AssignProfessorManager( $g_obj_user, $g_obj_db );

process_post();

/*
* set header stuff
*/

$g_str_page_title = "Manage Professors";
$g_arr_scripts = array( 'manageprofessors.js', 'commonadmin.js' );
$g_arr_nav_links = $g_arr_nav_defined_links[$g_obj_user->int_privilege];
$g_str_script_block = "xgene360_cu.using( 'sortabletable' )";

require ( '../includes/header.inc.php' );

if ( $g_bln_display_content )
{
?>

<!-- Start Content -->
<div>

  <form id="ManageProfessorsForm" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>" enctype="multipart/form-data">

    <strong>
      Click on a professor to edit specific professor information. 
      <br />To delete a professor or several professors, click on the checkbox beside each professor to be deleted and press 'Delete Selected'. 
      <br />You can also import or export lists of professors.    
    </strong>    
    
    <br /><br />

    <table class="format" width="100%">
      
      <tr>
        <td>
          <input class="buttoninput" type="button" value="Add New" name="Command" onclick="displayCreateProfessor();" />&nbsp;
          <input class="buttoninput" type="button" value="Import List" name="Command" onclick="displayImportProfessor();" />&nbsp;
          <input class="buttoninput" type="submit" value="Export Selected" name="Command" onclick="return validateProfessorSelection(); " />&nbsp;          
          <input class="buttoninput" type="submit" value="Delete Selected" name="Command" onclick="return validateDeleteProfessor();" />
        </td>
        <td align="right">
          <select name="SelectedCourse" id="SelectedCourse">
            
<?php
  
echo( '<option selected="selected">&nbsp;Assign selected to course:&nbsp;&nbsp;&nbsp;&nbsp;</option>' );
echo( '<option>&nbsp;----------</option>' );   
echo( '<option>&nbsp;</option>' );

$res_courses = $g_obj_course_manager->view_courses();

for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $res_courses ); ++$i )
{
	$res_row = $g_obj_db->fetch( $res_courses );
	
	echo( '<option value="' . htmlspecialchars( $res_row->CourseId ) . '">&nbsp;' . htmlspecialchars( $res_row->Name ) . '&nbsp;</option>'."\n" );
}

?>
          
          </select>&nbsp;
          <input class="buttoninput" type="submit" name="Command" value="Assign" onclick="return validateAssignProfessorsToACourse();" />
        </td>
      </tr>

    </table>

    <table class="listing" id="ListOfProfessors">

      <tr>
        <th width="50"><input type="checkbox" id="UserIdSelectionToggle" onclick="xgene360_cu.checkAll( this, 'ProfessorId[]' );" /></th>
        <th width="150">CWL Username</th>
        <th width="150">First Name</th>
        <th >Last Name</th>
      </tr>

<?php

$res_professors = $g_obj_professor_manager->view_professors();

for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $res_professors ); ++$i )
{
	$res_row = $g_obj_db->fetch( $res_professors );
	
	echo( '<tr onclick="openProfessorDetail( \'' . htmlspecialchars( $res_row->UserId, ENT_QUOTES ) . '\' );" onmouseover="hightlightSelectedRow( this, true );" onmouseout="hightlightSelectedRow( this, false );">' . "\n" );
	echo( '<td onmouseover="xgene360_cu.stopPropagation( event );" onclick="xgene360_cu.stopPropagation( event );"><input type="checkbox" name="ProfessorId[]" value="' . htmlspecialchars( $res_row->UserId ) . '" /></td>' . "\n" );
	echo( '<td>' . htmlspecialchars( $res_row->UserId ) . '</td>' . "\n" );
	echo( '<td>' . htmlspecialchars( $res_row->FirstName ) . '</td>' . "\n" );
	echo( '<td>' . htmlspecialchars( $res_row->LastName ) . '</td>' . "\n" );
	echo( '</tr>' . "\n" );
}
    
?>

    </table>

    <input class="buttoninput" type="button" value="Add New" name="Command" onclick="displayCreateProfessor();" />&nbsp;
    <input class="buttoninput" type="button" value="Import List" name="Command" onclick="displayImportProfessor();" />&nbsp;
    <input class="buttoninput" type="submit" value="Export Selected" name="Command" onclick="return validateProfessorSelection();" />&nbsp;    
    <input class="buttoninput" type="submit" value="Delete Selected" name="Command" onclick="return validateDeleteProfessor();" />

    <div id="CreateProfessorDiv" style="display: none">

      <br /><br />

      <table class="box">

        <tr>
          <th>Create Professor</th>
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
                  <input class="buttoninput" type="submit" name="Command" id="CommandCreate" value="Create" onclick="return validateCreateProfessorForm();" />
                  &nbsp;<input class="buttoninput" type="reset" name="Reset" value="Reset" onclick="return resetCreateProfessorForm();" />
                </td>
              </tr>
            </table>

          </td>
        </tr>

      </table>

    </div>

    <div id="ImportProfessorDiv" style="display: none">

      <br /><br />

      <table class="box">

        <tr>
          <th>Import Professors</th>
        </tr>

        <tr>
          <td>
            Upload File [<a href="">Help?</a>]<br />
            <input class="fileinput" type="file" name="ImportProfessorFile" id="ImportProfessorFile" />
            <br />
            <input class="buttoninput" type="submit" name="Command" value="Import" onclick="return validateImportProfessor();" />
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
*    Purpose:           Process creating the professor
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_create_handler()
{
	global $g_obj_professor_manager;
	
	$str_user_name = PageHandler::get_post_value( 'Username' );
	$str_first_name = PageHandler::get_post_value( 'FirstName' );
	$str_last_name = PageHandler::get_post_value( 'LastName' );
	
	// verify the input
	if ( !isset($str_user_name) )
	{
		MessageHandler::add_message( MSG_FAIL, 'Please enter a valid CWL Username' );
		return;
	}
	
	$str_first_name = !isset($str_first_name) ? " " : $str_first_name;
	$str_last_name = !isset($str_last_name) ? " " : $str_last_name;

	// create a new professor
	if ( $g_obj_professor_manager->create_user( $str_user_name, UP_PROFESSOR,  $str_first_name, $str_last_name) )
	{
			MessageHandler::add_message( MSG_SUCCESS, 'Successfully created an account for Professor "' . $str_user_name );
	}

	else
	{
			MessageHandler::add_message( MSG_FAIL, 'Failed to create an account for Professor "' . $str_user_name . '"' );
	}
}

/**  Function: void on_delete_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process deleting the professors
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_delete_handler()
{
	global $g_obj_professor_manager;
	
	$arr_professor_list = PageHandler::get_post_value( 'ProfessorId' );
	
	if ( $arr_professor_list == null )
	{
		MessageHandler::add_message( MSG_FAIL, "Please select at least one professor" );
		return;
	}
	
	$arr_professor_names = $g_obj_professor_manager->user_names_list( $arr_professor_list );
	
	$arr_success = array();
	$arr_fail = array();
	
	for ( $i = 0; $i < count( $arr_professor_names ); ++$i )
	{
		if ( $g_obj_professor_manager->delete_user( $arr_professor_names[$i][0] ) )
		{
			array_push( $arr_success, $arr_professor_names[$i][0] );
		}
		
		else
		{
			array_push( $arr_fail, $arr_professor_names[$i][0] );
		}
	}
	
	if ( count( $arr_success ) != 0 )
	{
		$str_message = PageHandler::display_users_cwl( 'Successfully deleted', $arr_success );
		
		MessageHandler::add_message( MSG_SUCCESS, $str_message );
	}
	
	if ( count( $arr_fail ) != 0 )
	{
		$str_message = PageHandler::display_users_cwl( 'Failed to delete', $arr_fail );
		
		MessageHandler::add_message( MSG_FAIL, $str_message );
	}
}

/**  Function: void on_assign_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process assigning the professors
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_assign_handler()
{
	global $g_obj_professor_manager, $g_obj_assign_professor_manager;
	
	$arr_professor_list = PageHandler::get_post_value( 'ProfessorId' );	
	$int_selected_course_id = PageHandler::get_post_value( 'SelectedCourse' );
	
	if ( !isset( $arr_professor_list ) || !isset( $int_selected_course_id ) )
	{
		MessageHandler::add_message( MSG_FAIL, "Please select at least one professor and select a course" );
		return;
	}
	
	$arr_professor_names = $g_obj_professor_manager->user_names_list( $arr_professor_list );
	
	$arr_success = array();
	$arr_fail = array();
	
	for ( $i = 0; $i < count( $arr_professor_names ); ++$i )
	{
		if ( $g_obj_assign_professor_manager->assign_professor( $arr_professor_names[$i][0], $int_selected_course_id ) )
		{
			array_push( $arr_success, $arr_professor_names[$i][0] );
		}
		
		else
		{
			array_push( $arr_fail, $arr_professor_names[$i][0] );
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
*    Purpose:           Process importing the professors list
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_import_handler()
{
	if ( !isset( $_FILES['ImportProfessorFile'] ) )
	{
		MessageHandler::add_message( MSG_FAIL, 'Please select a file' );
	}
	
	else
	{
		if ( !is_uploaded_file( $_FILES['ImportProfessorFile']['tmp_name'] ) )
		{
			MessageHandler::add_message( MSG_FAIL, 'The file cannot be retrieved' );
		}
		
		else
		{
			FileHandler::import_professor_list( $_FILES['ImportProfessorFile']['tmp_name']);
		}
	}
}

/**  Function: void on_export_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process exporting the professors list
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_export_handler()
{
	$arr_professor_list = PageHandler::get_post_value( 'ProfessorId' );
	
	if ( $arr_professor_list == null )
	{
		MessageHandler::add_message( MSG_FAIL, "Please select at least one professor" );
		return;
	}
	
	else
	{
		FileHandler::export_professor_list( $arr_professor_list );
	}
}

?>
