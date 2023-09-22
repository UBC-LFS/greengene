<?php

/*
* include necessary files
*/

require_once( '../includes/global.inc.php' );
require_once( '../includes/classes/db/problemmanager.class.php' );

/*
* initialize common stuff
*/

$g_obj_db = null;
$g_obj_lock = null;
$g_str_serial = null;
$g_obj_user = null;

$pageHandler = (new PageHandler);
$pageHandler -> initialize();
$pageHandler -> check_permission( array( UP_ADMINISTRATOR, UP_PROFESSOR, UP_TA ) );

// PageHandler::initialize();
// PageHandler::check_permission( array( UP_ADMINISTRATOR, UP_PROFESSOR, UP_TA ) );

$g_obj_problem_manager = new ProblemManager( $g_obj_user, $g_obj_db );
$g_str_script_block = "xgene360_cu.using( 'sortabletable' )";

process_post();

/*
* set header stuff
*/

$g_str_page_title = "Manage Problems";
$g_arr_scripts = array( 'manageproblems.js', 'commonadmin.js' );
$g_arr_nav_links = $g_arr_nav_defined_links[$g_obj_user->int_privilege];
$g_str_script_block = "xgene360_cu.using( 'sortabletable' )";

require_once( '../includes/header.inc.php' );

if ( $g_bln_display_content )
{
?>

<!-- Start Content -->
<div>

  <form id="ManageProblemsForm" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>">

    <strong>
      Click on a problem to edit specific problem information. 
      <br />To delete a problem or several problems, click on the checkbox beside each problem to be deleted and press 'Delete Selected Problems'.
    </strong>
    <br /><br />

<?php

if ( $g_obj_user->int_privilege != UP_TA )
{
?>
    <table class="format" width="100%">
      <tr>
        <td>
          <input class="buttoninput" type="submit" value="Create New Problem" name="Command" onclick="window.location='createproblem.php'; return false;"/>&nbsp;
          <input class="buttoninput" type="submit" value="Delete Selected Problems" name="Command" onclick="return validateDeleteProblem();" />
        </td>
      </tr>
    </table>
<?php
}
?>
    <table class="listing" id="ListOfProblems">
      <tr>
        <th width="50"><input type="checkbox" id="ProblemIdSelectionToggle" onclick="xgene360_cu.checkAll( this, 'ProblemId[]' );" /></th>
        <th width="150">Problem</th>
		<th width="100">Course</th>
        <th colspan="2"># Students<br />(submitted/total)</th>
      </tr>
          
<?php

$res_problems = $g_obj_problem_manager->view_problems();


if ($res_problems != NULL) { // added condition - prevents error if no courses available (added during PHP8 migration)
	for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $res_problems ); ++$i )
	{
		$res_row = $g_obj_db->fetch( $res_problems );

		echo( '<tr onclick="openProblemDetail( \'' . htmlspecialchars( $res_row->problem_id, ENT_QUOTES ) . '\' );" onmouseover="hightlightSelectedRow( this, true );" onmouseout="hightlightSelectedRow( this, false );">' . "\n" );
		echo( '<td onmouseover="xgene360_cu.stopPropagation( event );" onclick="xgene360_cu.stopPropagation( event );"><input type="checkbox" name="ProblemId[]" value="' . htmlspecialchars( $res_row->problem_id ) . '" /></td>'."\n" );
		echo( '<td>' . htmlspecialchars( $res_row->problem_name ) . '</td>'."\n" );
		echo( '<td>' . htmlspecialchars( $res_row->Name ). '</td>'."\n" );
		echo( '<td>' . $res_row->submit_count . '/' . htmlspecialchars( $res_row->student_count ) . '<td>&nbsp;<input class="buttoninput" type="button" value="View Submitted" onclick="openSolutions( event, ' . htmlspecialchars( $res_row->problem_id ) . ' );" /></td>'."\n" );
		echo( '</tr>' . "\n" );
	}
}
?>
                 
    </table>
<?php

if ( $g_obj_user->int_privilege != UP_TA )
{
?>    
    <input class="buttoninput" type="submit" value="Create New Problem" name="Command" onclick="window.location='createproblem.php'; return false;"/>&nbsp;
    <input class="buttoninput" type="submit" value="Delete Selected Problems" name="Command" onclick="return validateDeleteProblem();" />
<?php
}
?>
  
    <input type="hidden" name="SerialId" id="SerialId" value="<?= $g_str_serial ?>"/>

  </form>

</div>
<!-- End Content -->

<?php 
}

require_once('../includes/footer.inc.php'); 

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
	
	if ( isset( $_POST['Command'] ) && $g_obj_lock->page_lock( $pageHandler -> get_post_value( 'SerialId' ) ) )
	{
		$str_command = $_POST['Command'];
	  
		switch ( $str_command )
		{		
			case 'Delete Selected Problems':
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

/**  Function: void on_delete_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process deleting the problems
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_delete_handler()
{
	global $g_obj_problem_manager;
	
	// $arr_problem_list = PageHandler::get_post_value( 'ProblemId' );
	$arr_problem_list = $pageHandler -> get_post_value( 'ProblemId' );
	
	if ( $arr_problem_list == null )
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, "Please select at least one problem" );
		return;
	}
	
	$arr_success = array();
	$arr_fail = array();
	
	foreach( $arr_problem_list as $int_problem_id )
	{
		if ( $g_obj_problem_manager->delete_problem( $int_problem_id ) )
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
		(new MessageHandler) ->  add_message( MSG_SUCCESS, 'Successfully deleted ' . count( $arr_success ) . ' problem(s)' );
	}
	
	if ( count( $arr_fail ) != 0 )
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, 'Failed to delete ' . count( $arr_fail ) . ' problem(s)' );
	}
}

?>
