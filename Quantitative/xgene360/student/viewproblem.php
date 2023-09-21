<?php

/*
* include necessary files
*/

require_once( '../includes/global.inc.php' );
require_once( '../includes/classes/db/problemmanager.class.php' );
require_once( '../includes/classes/db/generationmanager.class.php' );
require_once( '../includes/classes/db/solutionmanager.class.php' );

/*
* necessary id
*/

$g_str_parent_page = './viewproblems.php';

PageHandler::check_necessary_id( array( 'ProblemId' ), $g_str_parent_page );

/*
* initialize common stuff
*/

$g_obj_db = null;
$g_obj_lock = null;
$g_str_serial = null;
$g_obj_user = null;

PageHandler::initialize();
PageHandler::check_permission( array( UP_STUDENT ) );

$g_obj_problem_manager = new ProblemManager( $g_obj_user, $g_obj_db );
$g_obj_generation_manager = new GenerationManager( $g_obj_user, $g_obj_db );
$g_obj_solution_manager = new SolutionManager( $g_obj_user, $g_obj_db );

/*
* required info
*/

$g_int_problem_id = $_GET['ProblemId'];

verify_problem_exists();

process_post();

/*
* set header stuff
*/

$g_str_page_title = "View Problem (Generation History)";
$g_arr_nav_links = $g_arr_nav_defined_links[$g_obj_user->int_privilege];
$g_arr_styles = array( 'histogram.css' );
$g_arr_scripts = array( 'histogram.js', 'plant.js', 'viewproblem.js' );

generate_script_block();

require_once( '../includes/header.inc.php' );

if ( $g_bln_display_content )
{
?>

<!-- Start Content -->
<div>

  <div>
  
    <input class="buttonback" type="button" value="&lt;&nbsp;&nbsp;Back to Problem Listing" onclick="window.location='<?= $g_str_parent_page ?>';" />
    <br /><br /><br />  
  
    <table border="0" cellpadding="0" cellspacing="0" class="box">
      
      <tr>
        <td>      

          <table class="format">

            <tr>
              <td width="110"><strong>PROBLEM: </strong></td>
              <td><?= $g_arr_problem_info->problem_name ?></td>
            </tr>
            <tr>
              <td><strong>DESCRIPTION: </strong></td>
              <td><?= $g_arr_problem_info->problem_desc ?></td>
            </tr>
          </table>

        </td>
      </tr>
    
    </table>
  
  </div>

  <br />

  
  <input class="buttoninput" type="button" value="Submit Solution" name="Command" onclick="displaySubmitSolution();" />
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
	$res_row = $g_obj_generation_manager->get_generation( $g_obj_user->str_username, $g_int_problem_id, $i );
	
	echo( '<tr onclick="openGenerationDetail( ' . htmlspecialchars( $g_int_problem_id ) .', ' . $i . ' );" onmouseover="hightlightSelectedRow( this, true );" onmouseout="hightlightSelectedRow( this, false );">' . "\n" );
	echo( '<td >'. $i .'&nbsp;/&nbsp;' . $g_arr_problem_info->max_cross . '</td>' . "\n" );
	
	$arr_trait_values = $g_obj_generation_manager->get_parents_trait_values( $g_obj_user->str_username, $g_int_problem_id, $i );
	
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
			
			$str_sd_A = PageHandler::format_precision( $dbl_sd_A, 3 );
			$str_sd_B = PageHandler::format_precision( $dbl_sd_B, 3 );
		}
	}
	
	echo( '<td>' . PageHandler::format_precision( $dbl_mean_A, 3 ) . '</td>' . "\n" );
	echo( '<td>' . $str_sd_A . '</td>' . "\n" );
	echo( '<td>' . PageHandler::format_precision( $dbl_mean_B, 3 ) . '</td>' . "\n" );
	echo( '<td>' . $str_sd_B . '</td>' . "\n" );
	echo( '<td><div style="padding: 5px 0px;"><div id="histogramA'. $i .'"	></div></div></td>' . "\n" );
	echo( '<td><div style="padding: 5px 0px;"><div id="histogramB'. $i .'"></div></div></td>' . "\n" );
	echo( '</tr>' . "\n" );
}

?>          

  </table>
  
  <input class="buttoninput" type="button" value="Submit Solution" name="Command" onclick="displaySubmitSolution();" />
  
  <div id="SubmitSolutionDiv" style="display: none;">

    <form method="post" id="SubmitSolutionForm" action="<?php echo( $_SERVER['REQUEST_URI'] ); ?>">

    <br /><br />

    <table class="box">
      <tr>
      <th>Submit Problem Solution</th>
      </tr>

      <tr>
      <td>

        <table class="format">
        <tr>
          <td colspan="2"><h3>Trait A</h3></td>
          <td width="100">&nbsp;</td>
          <td colspan="2"><h3>Trait B</h3></td>
          <td width="50">&nbsp;</td>
        </tr>

        <tr>
          <td>Parent 1 Mean:</td>
          <td><input class="numberinput" type="text" name="TraitAParent1Mean" id="TraitAParent1Mean" /></td>
          <td>&nbsp;</td>
          <td>Parent 1 Mean:</td>
          <td><input class="numberinput" type="text" name="TraitBParent1Mean" id="TraitBParent1Mean" /></td>
          <td>&nbsp;</td>
        </tr>

        <tr>
          <td>Parent 2 Mean:</td>
          <td><input class="numberinput" type="text" name="TraitAParent2Mean" id="TraitAParent2Mean" /></td>
          <td>&nbsp;</td>
          <td>Parent 2 Mean:</td>
          <td><input class="numberinput" type="text" name="TraitBParent2Mean" id="TraitBParent2Mean" /></td>
          <td>&nbsp;</td>
        </tr>

        <tr>
          <td>Trait Variance:</td>
          <td><input class="numberinput" type="text" name="TraitAVariance" id="TraitAVariance" /></td>
          <td>&nbsp;</td>
          <td>Trait Variance:</td>
          <td><input class="numberinput" type="text" name="TraitBVariance" id="TraitBVariance" /></td>
          <td>&nbsp;</td>
        </tr>

        <tr>
          <td>h<sup>2</sup>:</td>
          <td><input class="numberinput" type="text" name="TraitAHeritability" id="TraitAHeritability" /></td>
          <td>&nbsp;</td>
          <td>h<sup>2</sup>:</td>
          <td><input class="numberinput" type="text" name="TraitBHeritability" id="TraitBHeritability" /></td>
          <td>&nbsp;</td>
        </tr>

        <tr>
          <td width="115">Number of Genes:</td>
          <td><input class="numberinput" type="text" name="TraitANumberOfGenes" id="TraitANumberOfGenes" /></td>
          <td>&nbsp;</td>
          <td width="115">Number of Genes:</td>
          <td><input class="numberinput" type="text" name="TraitBNumberOfGenes" id="TraitBNumberOfGenes" /></td>
          <td>&nbsp;</td>
        </tr>

        <tr>
          <td colspan="6" height="8"></td>
        </tr>

          <tr>
          <td colspan="6" align="right">
          <input class="buttoninput" type="submit" value="Submit" name="Command" onclick="return validateSubmitSolutionForm();" />&nbsp;
          <input class="buttoninput" type="reset" value="Reset" name="Command" />
          </td>
        </tr>
        </table>

      </td>
      </tr>

    </table>

    <input type="hidden" name="SerialId" id="SerialId" value="<?= $g_str_serial ?>"/>

    </form>

  </div>
  
  <br /><br /><br />
  
  <input class="buttonback" type="button" value="&lt;&nbsp;&nbsp;Back to Problem Listing" onclick="window.location='<?= $g_str_parent_page ?>';" />
    
</div>
<!-- End Content -->

<?php
}

require( '../includes/footer.inc.php' );

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
	global $g_obj_problem_manager, $g_obj_generation_manager, $g_obj_db, $g_obj_user, $g_arr_problem_info, $g_int_problem_id, $g_int_number_of_generations;
	
	$res_problem = $g_obj_problem_manager->view_problem_details( $g_int_problem_id );
	
	if ( $g_obj_db->get_number_of_rows( $res_problem ) == 0 )
	{
		(new MessageHandler) ->  add_message( MSG_ERROR, 'The Problem does not exist' );
	}
	
	else
	{
		$g_arr_problem_info = $g_obj_db->fetch( $res_problem );
		
		if ( $g_obj_db->time() <= $g_arr_problem_info->start_date )
		{
			(new MessageHandler) ->  add_message( MSG_ERROR, "You cannot view this problem yet" );
			return;
		}
		
		// read the number of generations that the user has created
		$res_number_of_generations = $g_obj_generation_manager->get_number_of_generations( $g_obj_user->str_username, $g_int_problem_id );
		
		$res_row = $g_obj_db->fetch( $res_number_of_generations );
		
		$g_int_number_of_generations = $res_row->generation_count;
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
	global $g_str_script_block, $g_obj_db, $g_obj_user, $g_obj_generation_manager, $g_int_problem_id, $g_int_number_of_generations, $g_arr_problem_info;
	
	$g_str_script_block = '
 
	var objPlants = new Array();
	';

	// get all the generations
	for ( $i = 1; $i < $g_int_number_of_generations + 1; ++$i )
	{
		$res_generation = $g_obj_generation_manager->get_generation( $g_obj_user->str_username, $g_int_problem_id, $i );
		
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
	
	if ( isset( $_POST['Command'] ) && $g_obj_lock->page_lock( PageHandler::get_post_value( 'SerialId' ) ) )
	{
		$str_command = $_POST['Command'];
	  
		switch ( $str_command )
		{
			case 'Submit':
			{
				on_submit_handler();
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

/**  Function: void on_cross_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process submitting the solution
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_submit_handler()
{
	global $g_obj_db, $g_int_problem_id, $g_obj_solution_manager, $g_arr_problem_info;

	$dbl_Trait_A_Parent_A_Mean = PageHandler::get_post_value( 'TraitAParent1Mean' );
	$dbl_Trait_A_Parent_B_Mean = PageHandler::get_post_value( 'TraitAParent2Mean' );
	$dbl_Trait_B_Parent_A_Mean = PageHandler::get_post_value( 'TraitBParent1Mean' );
	$dbl_Trait_B_Parent_B_Mean = PageHandler::get_post_value( 'TraitBParent2Mean' );
	$dbl_Trait_A_variance = PageHandler::get_post_value( 'TraitAVariance' );
	$dbl_Trait_B_variance = PageHandler::get_post_value( 'TraitBVariance' );
	$dbl_Trait_A_heritability = PageHandler::get_post_value( 'TraitAHeritability' );
	$dbl_Trait_B_heritability = PageHandler::get_post_value( 'TraitBHeritability' );
	$int_Trait_A_number_of_genes = PageHandler::get_post_value( 'TraitANumberOfGenes' );
	$int_Trait_B_number_of_genes = PageHandler::get_post_value( 'TraitBNumberOfGenes' );
	
	if ( empty( $dbl_Trait_A_Parent_A_Mean ) || empty( $dbl_Trait_A_Parent_B_Mean ) || empty( $dbl_Trait_B_Parent_A_Mean ) ||
			empty( $dbl_Trait_B_Parent_B_Mean ) || empty( $dbl_Trait_A_variance ) || empty( $dbl_Trait_B_variance ) ||
			empty( $dbl_Trait_A_heritability ) || empty( $dbl_Trait_B_heritability ) || empty( $int_Trait_A_number_of_genes ) ||
			empty( $int_Trait_B_number_of_genes ) )
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, 'Please enter the necessary information' );
		return;
	}
	
	if ( $g_obj_solution_manager->add_solution( $g_int_problem_id,
													'',
													$int_Trait_A_number_of_genes,
													$int_Trait_B_number_of_genes,
													$dbl_Trait_A_heritability,
													$dbl_Trait_B_heritability,
													$dbl_Trait_A_variance,
													$dbl_Trait_B_variance,
													$dbl_Trait_A_Parent_A_Mean,
													$dbl_Trait_A_Parent_B_Mean,
													$dbl_Trait_B_Parent_A_Mean,
													$dbl_Trait_B_Parent_B_Mean ) )
	{
		(new MessageHandler) ->  add_message( MSG_SUCCESS, 'Successfully submit the solution' );
	}
	
	else
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, 'Failed to submit the solution' );
	}
}

?>
