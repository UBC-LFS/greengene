<?php 

/*
* include necessary files
*/

require_once( '../includes/global.inc.php' );
require_once( '../includes/classes/db/problemmanager.class.php' );
require_once( '../includes/classes/db/studentmanager.class.php' );
require_once( '../includes/classes/db/generationmanager.class.php' );
require_once( '../includes/classes/db/solutionmanager.class.php' );
require_once( '../includes/classes/db/assignstudentmanager.class.php' );

/*
* necessary id
*/

$g_str_parent_page = './managestudents.php';

(new PageHandler) -> check_necessary_id( array( 'StudentId' ), $g_str_parent_page );
(new PageHandler) -> check_necessary_id( array( 'ProblemId' ), $g_str_parent_page );

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

$g_obj_problem_manager = new ProblemManager( $g_obj_user, $g_obj_db );
$g_obj_student_manager = new StudentManager( $g_obj_user, $g_obj_db );
$g_obj_generation_manager = new GenerationManager( $g_obj_user, $g_obj_db );
$g_obj_assign_student_manager = new AssignStudentManager( $g_obj_user, $g_obj_db );
$g_obj_solution_manager = new SolutionManager( $g_obj_user, $g_obj_db );

/*
* required info
*/

$g_str_student_id = $_GET['StudentId'];
$g_int_problem_id = $_GET['ProblemId'];

$bln_problem_exists = verify_problem_exists();
verify_student_exists();

process_post();

/*
* set header stuff
*/

$g_str_page_title = "Manage Students &gt; View Progress";
$g_arr_styles = array( 'histogram.css' );
$g_arr_scripts = array( 'commonadmin.js', 'histogram.js', 'plant.js' );
$g_arr_nav_links = $g_arr_nav_defined_links[$g_obj_user->int_privilege];

if ($bln_problem_exists) {
	generate_script_block();
}
require_once( '../includes/header.inc.php' );

if ( $g_bln_display_content )
{
?>

<!-- Start Content -->
<div>

  <form method="post" id="UpdateProgressForm" action="<?= htmlspecialchars( $_SERVER['REQUEST_URI'] ) ?>">

    <input class="buttonback" type="button" value="&lt;&nbsp;&nbsp;Back to Solution Listing" onclick="openSolutions( event, <?= $g_int_problem_id ?>);" /><br /><br />
    <input class="buttonback" type="button" value="&lt;&nbsp;&nbsp;Back to Problem" onclick="openProblemDetail( <?= $g_int_problem_id ?>);" />
    <br /><br /><br />

    <table class="box">

      <tr>
        <th>Student Information</th>
      </tr>

      <tr>
        <td>

<?php

$g_res_student_solution = $g_obj_solution_manager->view_student_solution( $g_str_student_id, $g_int_problem_id );

?>
          <table>
			<tr>
              <td>CWL Username:</td>
              <td><?= $g_arr_student_info->UserId ?></td>
			</tr>

            <tr>
              <td width="100">Name:</td>
              <td><?= $g_arr_student_info->FirstName ?>, <?= $g_arr_student_info->LastName ?></td>
            </tr>

            
              
<?php

if ( $g_obj_db->get_number_of_rows( $g_res_student_solution ) == 0 )
{
?>
            <tr>
              <td>&nbsp;</td>
              <td><strong>Solution has not been submitted yet</strong></td>
            </tr>
<?php
}

else
{
	$res_row = $g_obj_db->fetch( $g_res_student_solution );

?>
            <tr>
              <td>Handin Date:</td>
              <td><?= (new PageHandler) -> format_date( $res_row->hand_in_date ) ?></td>
            </tr>
            
            <tr>
              <td colspan="2">
              
                <table class="format">

                  <tr>
                    <td width="165">&nbsp;</td>
                    <td><h4><?= $g_arr_problem_info->trait_A_name ?> (<?= $g_arr_problem_info->trait_A_unit ?>)</h4></td>
                    <td width="30">&nbsp;</td>
                    <td><h4><?= $g_arr_problem_info->trait_B_name ?> (<?= $g_arr_problem_info->trait_B_unit ?>)</h4></td>
                  </tr>

                  <tr>
                    <td width="170">Parent 1 Mean:</td>
                    <td><?= $res_row->trait_A_parent_A_mean ?></td>
                    <td>&nbsp;</td>
                    <td><?= $res_row->trait_B_parent_A_mean ?></td>
                  </tr>

                  <tr>
                    <td>Parent 2 Mean:</td>
                    <td><?= $res_row->trait_A_parent_B_mean ?></td>
                    <td>&nbsp;</td>
                    <td><?= $res_row->trait_B_parent_B_mean ?></td>
                  </tr>

                  <tr>
                    <td>Enviromental Variance:&nbsp;</td>
                    <td><?= $res_row->trait_A_var ?></td>
                    <td>&nbsp;</td>
                    <td><?= $res_row->trait_B_var ?></td>
                  </tr>

                  <tr>
                    <td>h<sup>2</sup>:&nbsp;</td>
                    <td><?= $res_row->trait_A_h2 ?></td>
                    <td>&nbsp;</td>
                    <td><?= $res_row->trait_B_h2 ?></td>
                  </tr>

                  <tr>
                    <td>Number of Genes:</td>
                    <td><?= $res_row->trait_A_number_of_genes ?></td>
                    <td>&nbsp;</td>
                    <td><?= $res_row->trait_B_number_of_genes ?></td>
                  </tr>

                  <tr>
                    <td colspan="4" height="8"></td>
                  </tr>

                  <tr>
                    <td>Range of Acceptance:<div style="font-size: 0.8em;">(for auto-submitted solutions)</div></td>
                    <td colspan="3"><?= $g_arr_problem_info->range_of_acceptance ?>%</td>
                  </tr>

                  <tr>
                    <td><strong>Reset Progress:</strong></td>
                    <td colspan="3"><input class="buttoninput" type="submit" name="Command" value="Reset Progress" onclick="return confirm( 'Are you sure you want to reset the progress for this student?\n\nThe generation history will be deleted!' );" /></td>
                  </tr>

                </table>

<?php
}
?>
              </td>
            </tr>

          </table>

        </td>
      </tr>

    </table>
    <br /><br />
    <table border="0" cellpadding="0" cellspacing="0" class="listing">
      <tr>
        <th width="150">Breeding History</th>
        <th width="100">Parent A Mean</th>
        <th width="80">Parent A SD</th>
        <th width="100">Parent B Mean</th>
        <th width="80">Parent B SD</th>
        <th width="200">Trait - <?= $g_arr_problem_info->trait_A_name ?></th>
        <th>Trait - <?= $g_arr_problem_info->trait_B_name ?></th>
      </tr>
                    
<?php

for ( $i = 1; $i < $g_int_number_of_generations + 1; ++$i )
{
	$res_row = $g_obj_generation_manager->get_generation( $g_str_student_id, $g_int_problem_id, $i );
	
	echo( '<tr>' . "\n" );
	echo( '<td>'. $i .'&nbsp;/&nbsp;' . $g_arr_problem_info->max_cross . '</td>' . "\n" );
	
	$arr_trait_values = $g_obj_generation_manager->get_parents_trait_values( $g_str_student_id, $g_int_problem_id, $i );
	
	$dbl_mean_A = 0.0;
	$dbl_mean_B = 0.0;
	$str_sd_A = "N/A";
	$str_sd_B = "N/A";
	
	if ( count( $arr_trait_values ) != 0 )
	{
		// calculate mean
		for ( $j = 0; $j < count( $arr_trait_values ); ++$j )
		{
			$dbl_mean_A += $arr_trait_values[$j][0];
			$dbl_mean_B += $arr_trait_values[$j][1];
		}
		
		$dbl_mean_A /= count( $arr_trait_values );
		$dbl_mean_B /= count( $arr_trait_values );
		
		$dbl_sd_A = 0.0;
		$dbl_sd_B = 0.0;
		
		if ( count( $arr_trait_values ) > 1 )
		{
			for ( $j = 0; $j < count( $arr_trait_values ); ++$j )
			{
				$dbl_sd_A += ( $arr_trait_values[$j][0] - $dbl_mean_A ) * ( $arr_trait_values[$j][0] - $dbl_mean_A );
				$dbl_sd_B += ( $arr_trait_values[$j][1] - $dbl_mean_B ) * ( $arr_trait_values[$j][1] - $dbl_mean_B );
			}
			
			$dbl_sd_A /= ( count( $arr_trait_values ) - 1 );
			$dbl_sd_B /= ( count( $arr_trait_values ) - 1 );
			
			$dbl_sd_A = sqrt( $dbl_sd_A );
			$dbl_sd_B = sqrt( $dbl_sd_B );
			
			$str_sd_A = (new PageHandler) -> format_precision( $dbl_sd_A, 3 );
			$str_sd_B = (new PageHandler) -> format_precision( $dbl_sd_B, 3 );
		}
	}
	
	echo( '<td>' . (new PageHandler) -> format_precision( $dbl_mean_A, 3 ) . '</td>' . "\n" );
	echo( '<td>' . $str_sd_A . '</td>' . "\n" );
	echo( '<td>' . (new PageHandler) -> format_precision( $dbl_mean_B, 3 ) . '</td>' . "\n" );
	echo( '<td>' . $str_sd_B . '</td>' . "\n" );
	echo( '<td><div style="padding: 5px 0px;"><div id="histogramA' . $i . '"></div></div></td>' . "\n" );
	echo( '<td><div style="padding: 5px 0px;"><div id="histogramB' . $i . '"></div></div></td>' . "\n" );
	echo( '</tr>' . "\n" );
}

?>          

    </table>

    <br /><br />
    <input class="buttonback" type="button" value="&lt;&nbsp;&nbsp;Back to Problem" onclick="openProblemDetail( <?= $g_int_problem_id ?>);" />
    <br /><br />
    <input class="buttonback" type="button" value="&lt;&nbsp;&nbsp;Back to Solution Listing" onclick="openSolutions( event, <?= $g_int_problem_id ?>);" />
   


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
*                       Set the generations information to
*                       $g_int_number_of_generations
*/
function verify_problem_exists()
{
	global $g_obj_problem_manager, $g_obj_generation_manager, $g_obj_db, $g_str_student_id, $g_arr_problem_info, $g_int_problem_id, $g_int_number_of_generations;
	
	$res_problem = $g_obj_problem_manager->view_problem_details( $g_int_problem_id );
	
	if ( $g_obj_db->get_number_of_rows( $res_problem ) == 0 )
	{
		(new MessageHandler) ->  add_message( MSG_ERROR, 'The Problem does not exist' );
		return false;
	}
	
	else
	{
		$g_arr_problem_info = $g_obj_db->fetch( $res_problem );
		
		// read the number of generations that the user has created
		$res_number_of_generations = $g_obj_generation_manager->get_number_of_generations( $g_str_student_id, $g_int_problem_id );
		
		$res_row = $g_obj_db->fetch( $res_number_of_generations );
		
		$g_int_number_of_generations = $res_row->generation_count;
		return true;
	}
}

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
		(new MessageHandler) ->  add_message( MSG_ERROR, 'The Student does not exist' );
	}
	
	else
	{
		$g_arr_student_info = $g_obj_db->fetch( $res_student );
	}
}

/**  Function: void generate_script_block()
*    ---------------------------------------------------------------- 
*    Purpose:           Generate the javascript block containing
*                       generation information
*    Arguments:         None
*                       
*    Returns/Assigns:   Set the generation data to $g_str_script_block
*/
function generate_script_block()
{
	global $g_str_script_block, $g_obj_db, $g_str_student_id, $g_obj_generation_manager, $g_int_problem_id, $g_int_number_of_generations, $g_arr_problem_info;
	
	$g_str_script_block = '
 
	var objPlants = new Array();
	';

	// get all the generations
	for ( $i = 1; $i < $g_int_number_of_generations + 1; ++$i )
	{
		$res_generation = $g_obj_generation_manager->get_generation( $g_str_student_id, $g_int_problem_id, $i );
		
		$g_str_script_block = $g_str_script_block . ' objPlants[' . $i . '] = new Array(';
		
		for ( $j = 0; $j < $g_obj_db->get_number_of_rows( $res_generation ); ++$j )
		{
			$res_plant = $g_obj_db->fetch( $res_generation );
			
			$g_str_script_block = $g_str_script_block . 'new xgene360_plant( \'' . $res_plant->plant_id . '\', ' . $res_plant->value_trait_A . ', ' . $res_plant->value_trait_B . ' )';
			
			if ( $j != $g_obj_db->get_number_of_rows( $res_generation ) - 1 )
			{
				$g_str_script_block = $g_str_script_block . ", ";
			}
		}
		
		$g_str_script_block = $g_str_script_block . ');' . "\n";
	}
	
	// calculate the fixed range
	$dbl_range = ( $g_arr_problem_info->trait_A_histogram_range + $g_arr_problem_info->trait_A_var ) / 100;
	$dbl_trait_A_min = min( $g_arr_problem_info->trait_A_parent_A_mean, $g_arr_problem_info->trait_A_parent_B_mean );
	$dbl_trait_A_min = $dbl_trait_A_min - $dbl_trait_A_min * $dbl_range;
	
	$dbl_trait_A_max = max( $g_arr_problem_info->trait_A_parent_A_mean, $g_arr_problem_info->trait_A_parent_B_mean );
	$dbl_trait_A_max = $dbl_trait_A_max + $dbl_trait_A_max * $dbl_range;

	$dbl_range = ( $g_arr_problem_info->trait_B_histogram_range + $g_arr_problem_info->trait_B_var ) / 100;
	$dbl_trait_B_min = min( $g_arr_problem_info->trait_B_parent_A_mean, $g_arr_problem_info->trait_B_parent_B_mean );
	$dbl_trait_B_min = $dbl_trait_B_min - $dbl_trait_B_min * $dbl_range;
	
	$dbl_trait_B_max = max( $g_arr_problem_info->trait_B_parent_A_mean, $g_arr_problem_info->trait_B_parent_B_mean );
	$dbl_trait_B_max = $dbl_trait_B_max + $dbl_trait_B_max * $dbl_range;
	
	$g_str_script_block = $g_str_script_block . '
	
	function loadHistograms()
	{
      var objHistogramA = new Array();
      var objHistogramB = new Array();

      for ( var i = 1; i < ' . ( $g_int_number_of_generations + 1 ) . '; ++i )
      {
        objHistogramA[i - 1] = new xgene360_histogram( \'traitA\', \'selectTraitA\' );
        objHistogramA[i - 1].setRange( ' . $dbl_trait_A_min . ', ' . $dbl_trait_A_max . ' );
        objHistogramA[i - 1].assignValues( objPlants[i], true );
        objHistogramA[i - 1].renderSimple( \'histogramA\' + i, 142, 100, 20 );
        objHistogramB[i - 1] = new xgene360_histogram( \'traitB\', \'selectTraitB\' );
        objHistogramB[i - 1].setRange( ' . $dbl_trait_B_min . ', ' . $dbl_trait_B_max . ' );
        objHistogramB[i - 1].assignValues( objPlants[i], false );
        objHistogramB[i - 1].renderSimple( \'histogramB\' + i, 142, 100, 20 );
      }
	}
	';

	$g_str_script_block  = $g_str_script_block . 'xgene360_cu.event.addDOMListener( window, \'onload\', loadHistograms );' . "\n";
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
			case 'Reset Progress':
			{
				on_reset_progress_handler();
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

/**  Function: void on_reset_progress_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process resetting the progress
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_reset_progress_handler()
{
	global $g_obj_assign_student_manager, $g_str_student_id, $g_int_problem_id;
	
	// reassign student to reset the progress
	if ( !$g_obj_assign_student_manager->unassign_student_from_problem( $g_str_student_id, $g_int_problem_id ) ||
			!$g_obj_assign_student_manager->assign_student_to_problem( $g_str_student_id, $g_int_problem_id ) )
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, 'Failed to reset the student progress' );
	}
	
	else
	{
		(new MessageHandler) ->  add_message( MSG_SUCCESS, 'Successfully reset the student progress' );
	}
}

?>
