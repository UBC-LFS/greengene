<?php 

/*
* include necessary files
*/

require_once( '../includes/global.inc.php' );
require_once( '../includes/classes/db/problemmanager.class.php' );
require_once( '../includes/classes/db/assignproblemmanager.class.php' );
require_once( '../includes/classes/db/assignstudentmanager.class.php' );
require_once( '../includes/classes/db/generationmanager.class.php' );

/*
* necessary id
*/

$g_str_parent_page = './manageproblems.php';

PageHandler::check_necessary_id( array( 'ProblemId' ), $g_str_parent_page );

/*
* initialize common stuff
*/

$g_obj_db = null;
$g_obj_lock = null;
$g_str_serial = null;
$g_obj_user = null;

PageHandler::initialize();
PageHandler::check_permission( array( UP_ADMINISTRATOR, UP_PROFESSOR, UP_TA ) );

$g_obj_problem_manager = new ProblemManager( $g_obj_user, $g_obj_db );
$g_obj_assign_problem_manager = new AssignProblemManager( $g_obj_user, $g_obj_db );
$g_obj_assign_student_manager = new AssignStudentManager( $g_obj_user, $g_obj_db );

/*
* required info
*/

$g_int_problem_id = $_GET["ProblemId"];

$g_bln_is_editable = ( $g_obj_user->int_privilege != UP_TA );

verify_problem_exists();

process_post();

$g_int_start_date = $g_arr_problem_info->start_date;
$g_int_due_date = $g_arr_problem_info->due_date;

/*
* set header stuff
*/

$g_str_page_title = "View Problem";
$g_arr_scripts = array( 'manageproblems.js', 'commonadmin.js' );
$g_arr_nav_links = $g_arr_nav_defined_links[$g_obj_user->int_privilege];
$g_str_script_block = "xgene360_cu.using( 'sortabletable' )";

require_once( '../includes/header.inc.php' );

if ( $g_bln_display_content )
{
?>

<!-- Start Content -->

<div>

  <form id="UpdateProblemForm" method="post" action="<?= $_SERVER['REQUEST_URI'] ?>">

    <input class="buttonback" type="button" value="&lt;&nbsp;&nbsp;Back to Problem Listing" onclick="window.location='<?= $g_str_parent_page ?>';" />
    <br /><br /><br />

    <table class="format">
      
<?php

if ( $g_bln_is_editable )
{
?>
      <tr>
        <td>
          <input class="buttoninput" type="submit" value="Update" name="Command" onclick="return validateUpdateProblemForm();" />&nbsp;
          <input class="buttoninput" type="reset" value="Reset To Saved" name="Command" onclick="return resetUpdateProblemForm();" />
        </td>
      </tr>
<?php
}
?>
      <tr>
        <td>      
          
          <table class="box" style="width: 100%">
            
            <tr>
              <th>Problem Information</th>
            </tr>
            
            <tr>
              <td>
                
                <table>
                  <tr>
                    <td width="170">Problem Name:</td>
                    <td><input class="longtextinput" type="text" name="ProblemName" id="ProblemName" value="<?= htmlspecialchars( $g_arr_problem_info->problem_name ) ?>" /></td>
                  </tr>
                  <tr>
                    <td style="vertical-align: top;">Problem Description:</td>
                    <td><textarea class="textareainput" name="ProblemDescription" id="ProblemDescription" cols="60" rows="10"><?= htmlspecialchars( $g_arr_problem_info->problem_desc ) ?></textarea></td>
                  </tr>
                  <tr>
                    <td>Course:</td>
                    <td><?= htmlspecialchars( $g_arr_problem_info->Name ) ?></td>
                  </tr>
                  <tr>
                    <td>Author:</td>
                    <td><?= htmlspecialchars( $g_arr_problem_info->LastName ) . ',&nbsp;' . htmlspecialchars( $g_arr_problem_info->FirstName ) ?></td>
                  </tr>
                  <tr>
                    <td colspan="2" height="10">&nbsp;</td>
                  </tr>
                  <tr>
                    <td>Start Date:</td>
                    <td>
<?= PageHandler::generate_month( 'StartDateMonth', date( 'n', $g_int_start_date ) ); ?>

                    &nbsp;

<?= PageHandler::generate_day( 'StartDateDay', date( 'j', $g_int_start_date ) ); ?>

                    &nbsp;
                    
<?= PageHandler::generate_year( 'StartDateYear', date( 'Y' ), date( 'Y', $g_int_start_date ) ); ?>

                    &nbsp;&nbsp;&nbsp;

<?= PageHandler::generate_hour( 'StartDateHour', date( 'H', $g_int_start_date ) ); ?>

                    :
                  
<?= PageHandler::generate_minute( 'StartDateMinute', date( 'i', $g_int_start_date ) ); ?>

                    </td>
                  </tr>
                  <tr>
                    <td>Due Date:</td>
                    <td>

<?= PageHandler::generate_month( 'DueDateMonth', date( 'n', $g_int_due_date ) ); ?>

                    &nbsp;

<?= PageHandler::generate_day( 'DueDateDay', date( 'j', $g_int_due_date ) ); ?>

                    &nbsp;

<?= PageHandler::generate_year( 'DueDateYear', date( 'Y' ), date( 'Y', $g_int_due_date ) ); ?>

                    &nbsp;&nbsp;&nbsp;
                  
<?= PageHandler::generate_hour( 'DueDateHour', date( 'H', $g_int_due_date ) ); ?>

                    :
                  
<?= PageHandler::generate_minute( 'DueDateMinute', date( 'i', $g_int_due_date ) ); ?>

                    </td>
                  </tr>

                  <tr>
                    <td colspan="2" height="10">&nbsp;</td>
                  </tr>
                  <tr>
                    <td>First Trait Name:</td>
                    <td><input class="longtextinput" type="text" name="TraitAName" id="TraitAName" value="<?= htmlspecialchars( $g_arr_problem_info->trait_A_name ) ?>" onkeyup="livePreview( 'TraitAName', 'TraitAPreview', 'Trait - ', 'First Trait' );" /></td>
                  </tr>
                  <tr>
                    <td>Second Trait Name:</td>
                    <td><input class="longtextinput" type="text" name="TraitBName" id="TraitBName" value="<?= htmlspecialchars( $g_arr_problem_info->trait_B_name ) ?>" onkeyup="livePreview( 'TraitBName', 'TraitBPreview', 'Trait - ', 'Second Trait' );" /></td>
                  </tr>     
                  <tr>
                    <td colspan="2" height="15">&nbsp;</td>
                  </tr>                               
                  <tr>
                    <td>Max # of Crosses:</td>
                    <td><input class="numberinput" type="text" name="MaxCross" id="MaxCross" value="<?= htmlspecialchars( $g_arr_problem_info->max_cross ) ?>" />&nbsp;plants</td>
                  </tr>
                  <tr>
                    <td>Offspring per Cross:</td>
                    <td><input class="numberinput" type="text" name="OffspringPerCross" id="OffspringPerCross" value="<?= htmlspecialchars( $g_arr_problem_info->number_of_progeny_per_cross ) ?>" />&nbsp;plants</td>
                  </tr>
                  <tr>
                    <td>Displayed Plants per Cross:</td>
                    <td><input class="numberinput" type="text" name="PlantsDisplayed" id="PlantsDisplayed" value="<?= htmlspecialchars( $g_arr_problem_info->number_of_displayed_plants ) ?>" />&nbsp;plants</td>
                  </tr>
                  <tr>
                    <td>Range of Acceptance:<div style="font-size: 0.8em;">(for auto-submitted solutions)</div></td>
                    <td><input class="numberinput" type="text" name="RangeOfAcceptance" id="RangeOfAcceptance" value="<?= htmlspecialchars( $g_arr_problem_info->range_of_acceptance ) ?>" />&nbsp;%</td>
                  </tr>
                  <tr>
                    <td height="8"></td>
                  </tr>
                </table>

              </td>
            </tr>
          </table>

        </td>
      </tr>

      <tr>
        <td>
        
          <table class="box">

            <tr>
              <th>Trait Information</th>
            </tr>

            <tr>
              <td>

                <table class="format">

                  <tr>
                    <td colspan="2"><h3 id="TraitAPreview">Trait - <?= htmlspecialchars( $g_arr_problem_info->trait_A_name ) ?></h3></td>
                    <td width="100">&nbsp;</td>
                    <td colspan="2"><h3 id="TraitBPreview">Trait - <?= htmlspecialchars( $g_arr_problem_info->trait_B_name ) ?></h3></td>
                    <td width="50">&nbsp;</td>
                  </tr>

                  <tr>
                    <td width="150">Parent 1 Mean:</td>
                    <td><input class="numberinput" type="text" name="TraitAParent1Mean" id="TraitAParent1Mean" value="<?= htmlspecialchars( $g_arr_problem_info->trait_A_parent_A_mean ) ?>" /></td>
                    <td>&nbsp;</td>
                    <td width="150">Parent 1 Mean:</td>
                    <td><input class="numberinput" type="text" name="TraitBParent1Mean" id="TraitBParent1Mean" value="<?= htmlspecialchars( $g_arr_problem_info->trait_B_parent_A_mean ) ?>" /></td>
                    <td>&nbsp;</td>
                  </tr>

                  <tr>
                    <td>Parent 2 Mean:</td>
                    <td><input class="numberinput" type="text" name="TraitAParent2Mean" id="TraitAParent2Mean" value="<?= htmlspecialchars( $g_arr_problem_info->trait_A_parent_B_mean ) ?>" /></td>
                    <td>&nbsp;</td>
                    <td>Parent 2 Mean:</td>
                    <td><input class="numberinput" type="text" name="TraitBParent2Mean" id="TraitBParent2Mean" value="<?= htmlspecialchars( $g_arr_problem_info->trait_B_parent_B_mean ) ?>" /></td>
                    <td>&nbsp;</td>
                  </tr>

                  <tr>
                    <td>Environmental Variance:</td>
                    <td><input class="numberinput" type="text" name="TraitAVariance" id="TraitAVariance" value="<?= htmlspecialchars( $g_arr_problem_info->trait_A_var ) ?>" /> %</td>
                    <td>&nbsp;</td>
                    <td>Environmental Variance:</td>
                    <td><input class="numberinput" type="text" name="TraitBVariance" id="TraitBVariance" value="<?= htmlspecialchars( $g_arr_problem_info->trait_B_var ) ?>" /> %</td>
                    <td>&nbsp;</td>
                  </tr>

                  <tr>
                    <td>h<sup>2</sup>:</td>
                    <td><?= htmlspecialchars( $g_arr_problem_info->trait_A_h2 ) ?></td>
                    <td>&nbsp;</td>
                    <td>h<sup>2</sup>:</td>
                    <td><?= htmlspecialchars( $g_arr_problem_info->trait_B_h2 ) ?></td>
                    <td>&nbsp;</td>
                  </tr>

                  <tr>
                    <td>Number of Genes:</td>
                    <td><input class="numberinput" type="text" name="TraitANumberOfGenes" id="TraitANumberOfGenes" value="<?= htmlspecialchars( $g_arr_problem_info->trait_A_number_of_genes ) ?>" /></td>
                    <td>&nbsp;</td>
                    <td>Number of Genes:</td>
                    <td><input class="numberinput" type="text" name="TraitBNumberOfGenes" id="TraitBNumberOfGenes" value="<?= htmlspecialchars( $g_arr_problem_info->trait_B_number_of_genes ) ?>" /></td>
                    <td>&nbsp;</td>
                  </tr>

                  <tr>
                    <td>Unit:</td>
                    <td><input class="textinput" type="text" name="TraitAUnit" id="TraitAUnit" value="<?= htmlspecialchars( $g_arr_problem_info->trait_A_unit ) ?>" /></td>
                    <td>&nbsp;</td>
                    <td>Unit:</td>
                    <td><input class="textinput" type="text" name="TraitBUnit" id="TraitBUnit" value="<?= htmlspecialchars( $g_arr_problem_info->trait_B_unit ) ?>" /></td>
                    <td>&nbsp;</td>
                  </tr>

                  <tr>
                    <td>Histogram Range:</td>
                    <td><input class="textinput" type="text" name="HistogramRangeA" id="HistogramRangeA" value="<?= htmlspecialchars( $g_arr_problem_info->trait_A_histogram_range ) ?>" />&nbsp;%</td>
                    <td>&nbsp;</td>
                    <td>Histogram Range:</td>
                    <td><input class="textinput" type="text" name="HistogramRangeB" id="HistogramRangeB" value="<?= htmlspecialchars( $g_arr_problem_info->trait_B_histogram_range ) ?>" />&nbsp;%</td>
                    <td>&nbsp;</td>
                  </tr>

                </table>

              </td>
            </tr>

          </table>

        </td>
      </tr>
<?php

if ( $g_bln_is_editable )
{
?>
      <tr>
        <td>
          <input type="hidden" name="StartDate" id="StartDate" value="" />
          <input type="hidden" name="DueDate" id="DueDate" value="" />
          <input class="buttoninput" type="submit" value="Update" name="Command" onclick="return validateUpdateProblemForm();" />&nbsp;
          <input class="buttoninput" type="reset" value="Reset To Saved" name="Command" onclick="return resetUpdateProblemForm();" />
        </td>
      </tr>
<?php
}
?>
      <tr>
        <td>

	         <br /><br />
          
          <strong>The following students have been assigned to this problem:</strong>

          <table class="listing" id="ListOfStudents">

            <tr>
              <th width="50"><input type="checkbox" id="UserIdSelectionToggle" onclick="xgene360_cu.checkAll( this, 'StudentId[]' );" /></th>
              <th width="150">CWL Username</th>
              <th width="150">Last Name</th>
              <th width="150">First Name</th>
              <th>Progress</th>
            </tr>
            
<?php

$res_students = $g_obj_assign_problem_manager->view_students_assigned_to_problem( $g_int_problem_id );

if ( $g_obj_db->get_number_of_rows( $res_students ) == 0 )
{
	echo( '<tr>' . "\n" );
	echo( '<td colspan="6">There are no student assigned to this problem</td>' . "\n" );
	echo( '</tr>' . "\n" );
}

for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $res_students ); ++$i )
{
	$res_row = $g_obj_db->fetch( $res_students );

	echo( '<tr onclick="openStudentDetail( \'' . htmlspecialchars( $res_row->UserId, ENT_QUOTES ) . '\' );" onmouseover="hightlightSelectedRow( this, true );" onmouseout="hightlightSelectedRow( this, false );">' . "\n" );
	echo( '<td onmouseover="xgene360_cu.stopPropagation( event );" onclick="xgene360_cu.stopPropagation( event );"><input type="checkbox" name="StudentId[]" value="' . htmlspecialchars( $res_row->UserId ) . '" /></td>' . "\n" );
	echo( '<td>' . htmlspecialchars( $res_row->UserId ) . '</td>' . "\n" );
	echo( '<td>' . htmlspecialchars( $res_row->LastName ) . '</td>' . "\n" );
	echo( '<td>' . htmlspecialchars( $res_row->FirstName ) . '</td>' . "\n" );
	echo( '<td><input class="buttoninput" type="button" value="View Progress" onclick="openProgress( event, ' . $g_int_problem_id . ', \'' . $res_row->UserId . '\' );" /></td>' . "\n" );
	echo( '</tr>' . "\n" );
}
	
?>
						
          </table>

<?php

if ( $g_obj_db->get_number_of_rows( $res_students ) != 0 )
{
?>
          <input class="buttoninput" type="submit" name="Command" value="Drop Selected Students" onclick="return validateDropStudent();" />
<?php
}
?>

        </td>
      </tr>

    </table>
    
    <br /><br /><br />
    
    <input class="buttonback" type="button" value="&lt;&nbsp;&nbsp;Back to Problem Listing" onclick="window.location='<?= $g_str_parent_page ?>';" />
    <input type="hidden" name="SerialId" id="SerialId" value="<?= $g_str_serial ?>" />
    
  </form>

</div>
<!-- End Content -->

<?php
}

require_once( '../includes/footer.inc.php' );

$g_obj_db->disconnect();

/**  Function: void verify_problem_exists()
*    ---------------------------------------------------------------- 
*    Purpose:           Verify the problem specified by ProblemId
*                       exists
*    Arguments:         None
*                       
*    Returns/Assigns:   Set the problem information to
*                       $g_arr_problem_info if the problem exists
*/
function verify_problem_exists()
{
	global $g_obj_problem_manager, $g_obj_db, $g_int_problem_id, $g_arr_problem_info;
	
	$res_problem = $g_obj_problem_manager->view_problem_details( $g_int_problem_id );
	
	if ( $g_obj_db->get_number_of_rows( $res_problem ) == 0 )
	{
		MessageHandler::add_message( MSG_ERROR, 'Either the problem does not exist or you do not have permission to see this problem' );
	}
	
	else
	{
		$g_arr_problem_info = $g_obj_db->fetch( $res_problem );
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
			
			case 'Drop Selected Students':
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
*    Purpose:           Process updating the problem
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_update_handler()
{
	global $g_bln_is_editable, $g_int_problem_id, $g_obj_problem_manager;
	
	if ( !$g_bln_is_editable )
	{
		MessageHandler::add_message( MSG_FAIL, 'You do not have permission to perform this operation' );
		return;
	}
	
	$str_problem_name = PageHandler::get_post_value( 'ProblemName' );
	$str_problem_description = PageHandler::get_post_value( 'ProblemDescription' );
	$str_Trait_A_name = PageHandler::get_post_value( 'TraitAName' );
	$str_Trait_B_name = PageHandler::get_post_value( 'TraitBName' );
	$str_Trait_A_unit = PageHandler::get_post_value( 'TraitAUnit' );
	$str_Trait_B_unit = PageHandler::get_post_value( 'TraitBUnit' );
	$int_Trait_A_number_of_genes = PageHandler::get_post_value( 'TraitANumberOfGenes' );
	$int_Trait_B_number_of_genes = PageHandler::get_post_value( 'TraitBNumberOfGenes' );
	$dbl_Trait_A_variance = PageHandler::get_post_value( 'TraitAVariance' );
	$dbl_Trait_B_variance = PageHandler::get_post_value( 'TraitBVariance' );
	$dbl_Trait_A_Parent_A_Mean = PageHandler::get_post_value( 'TraitAParent1Mean' );
	$dbl_Trait_A_Parent_B_Mean = PageHandler::get_post_value( 'TraitAParent2Mean' );
	$dbl_Trait_B_Parent_A_Mean = PageHandler::get_post_value( 'TraitBParent1Mean' );
	$dbl_Trait_B_Parent_B_Mean = PageHandler::get_post_value( 'TraitBParent2Mean' );
	$int_Histogram_A_Range = PageHandler::get_post_value( 'HistogramRangeA' );
	$int_Histogram_B_Range = PageHandler::get_post_value( 'HistogramRangeB' );
	$int_offspring_per_cross = PageHandler::get_post_value( 'OffspringPerCross' );
	$int_max_cross = PageHandler::get_post_value( 'MaxCross' );
	$int_plants_displayed = PageHandler::get_post_value( 'PlantsDisplayed' );
	$dbl_range_of_acceptance = PageHandler::get_post_value( 'RangeOfAcceptance' );
	$dat_start_date = PageHandler::get_post_value( 'StartDate' );
	$date_due_date  = PageHandler::get_post_value( 'DueDate' );
	
	if ( !isset( $str_problem_name ) || !isset( $str_problem_description ) || !isset( $str_Trait_A_name ) ||
			!isset( $str_Trait_B_name ) || !isset( $str_Trait_A_unit ) || !isset( $str_Trait_B_unit ) ||
			!isset( $int_Trait_A_number_of_genes ) || !isset( $int_Trait_B_number_of_genes ) ||
			!isset( $dbl_Trait_A_variance ) || !isset( $dbl_Trait_B_variance ) ||
			!isset( $dbl_Trait_A_Parent_A_Mean ) || !isset( $dbl_Trait_A_Parent_B_Mean ) ||
			!isset( $dbl_Trait_B_Parent_A_Mean ) || !isset( $dbl_Trait_B_Parent_B_Mean ) ||
			!isset( $int_Histogram_A_Range ) || !isset( $int_Histogram_B_Range ) ||
			!isset( $int_offspring_per_cross ) || !isset( $int_max_cross ) || !isset( $int_plants_displayed ) ||
			!isset( $dbl_range_of_acceptance ) || !isset( $dat_start_date ) || !isset( $date_due_date ) )
	{
		MessageHandler::add_message( MSG_FAIL, 'Please enter the necessary information' );
		return;
	}
	
	if ( $g_obj_problem_manager->modify_problem( $g_int_problem_id, $str_problem_name, $str_problem_description, 
												 $str_Trait_A_name, $str_Trait_B_name, $str_Trait_A_unit, 
												 $str_Trait_B_unit, $int_Trait_A_number_of_genes, $int_Trait_B_number_of_genes, 
												 $dbl_Trait_A_variance, $dbl_Trait_B_variance, $dbl_Trait_A_Parent_A_Mean,
												 $dbl_Trait_A_Parent_B_Mean, $dbl_Trait_B_Parent_A_Mean, $dbl_Trait_B_Parent_B_Mean,
												 $int_offspring_per_cross, $int_max_cross, $int_plants_displayed, $dbl_range_of_acceptance,
												 $int_Histogram_A_Range, $int_Histogram_B_Range, $dat_start_date, $date_due_date ) )
	{
		MessageHandler::add_message( MSG_SUCCESS, 'Successfully updated Problem "' . $str_problem_name . '"' );
	}
	
	else
	{
		MessageHandler::add_message( MSG_FAIL, 'Failed to update Problem "' . $str_problem_name . '"' );
	}
	
	// force to reload the problem
	verify_problem_exists();
}

/**  Function: void on_remove_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process removing the students from the 
*                       problem
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_remove_handler()
{
	global $g_bln_is_editable, $g_obj_assign_student_manager, $g_int_problem_id;
	
	if ( !$g_bln_is_editable )
	{
		MessageHandler::add_message( MSG_FAIL, 'You do not have permission to perform this operation' );
		return;
	}
	
	$arr_student_list = PageHandler::get_post_value( 'StudentId' );	
	
	if ( $arr_student_list == null )
	{
		MessageHandler::add_message( MSG_FAIL, "Please select at least one student" );
		return;
	}
	
	$arr_success = array();
	$arr_fail = array();
	
	foreach( $arr_student_list as $str_student_id )
	{
		if ( $g_obj_assign_student_manager->unassign_student_from_problem( $str_student_id, $g_int_problem_id ) )
		{
			array_push( $arr_success, $g_int_problem_id );
		}
		
		else
		{
			array_push( $arr_fail, $g_int_problem_id );
		}
	}
	
	if ( count( $arr_success ) != 0 )
	{
		MessageHandler::add_message( MSG_SUCCESS, 'Successfully removed ' . count( $arr_success ) . ' student(s) from this problem' );
	}
	
	if ( count( $arr_fail ) != 0 )
	{
		MessageHandler::add_message( MSG_FAIL, 'Failed to remove ' . count( $arr_fail ) . ' student(s) from this problem' );
	}
}

?>
