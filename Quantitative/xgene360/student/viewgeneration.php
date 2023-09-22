<?php 

/*
* include necessary files
*/

require_once( '../includes/global.inc.php' ); 
require_once( '../includes/classes/db/problemmanager.class.php' );
require_once( '../includes/classes/db/generationmanager.class.php' );

/*
* necessary id
*/


$g_str_parent_page = './viewproblems.php';

$pageHandler -> check_necessary_id( array( 'ProblemId' ), $g_str_parent_page );

/*
* initialize common stuff
*/

$g_obj_db = null;
$g_obj_lock = null;
$g_str_serial = null;
$g_obj_user = null;

// PageHandler::initialize();
// PageHandler::check_permission( array( UP_STUDENT ) );

$pageHandler = (new PageHandler);
$pageHandler -> initialize();
$pageHandler -> check_permission( array( UP_STUDENT ) );

$g_obj_problem_manager = new ProblemManager( $g_obj_user, $g_obj_db );
$g_obj_generation_manager = new GenerationManager( $g_obj_user, $g_obj_db );

/*
* required info
*/

$g_int_problem_id = $_GET['ProblemId'];

if ( !isset( $_GET['GenerationId'] ) )
{
	$g_int_generation_id = 1;
}

else
{
	$g_int_generation_id = $_GET['GenerationId'];
}

$g_bln_plants_image_loaded = ( (new CookieHandler) -> get_cookie_value( 'PlantsImageLoaded' ) == null );
(new CookieHandler) -> set_cookie_value( 'PlantsImageLoaded', true );

verify_problem_exists();

process_post();

/*
* set header stuff
*/

$g_str_page_title = "View Generation";
$g_arr_nav_links = $g_arr_nav_defined_links[$g_obj_user->int_privilege];
$g_arr_styles = array( 'histogram.css' );
$g_arr_scripts = array( 'histogram.js', 'plant.js', 'viewgeneration.js' );

generate_script_block();

require_once( '../includes/header.inc.php' );

if ( $g_bln_display_content )
{
?>

<!-- Start Content -->

<?php

	if ( $g_bln_plants_image_loaded )
	{
		// first time visit, reader all images
		$str_image_base = './images/';

		$arr_images = array( 'spin.gif', 'pots/pot_a.gif', 'pots/pot_b.gif', 'pots/pot_ab.gif', 
								'pots_alpha/pot_a.gif', 'pots_alpha/pot_b.gif', 'pots_alpha/pot_c.gif', 'pots_alpha/pot_ab.gif', 
								'shelf/shelf_bottomleft.gif', 'shelf/shelf_bottomright.gif', 'shelf/shelf_level.gif', 'shelf/shelf_sideleft.gif', 
								'shelf/shelf_sideleft.gif', 'shelf/shelf_topleft.gif', 'shelf/shelf_topright.gif' );

		echo( '<!-- Hack for IE -->' );
		echo( '<div style="display: none;">' );

		for ( $i = 0; $i < count( $arr_images ); ++$i )
		{
			echo( '<img src="' . $str_image_base.$arr_images[$i] . '" />' );
		}

		for ( $i = 1; $i < 11; ++$i )
		{
			$index = '00'.$i;
			$index = substr( $index, strlen( $index ) - 2 );
		        
			echo( '<img src="' . $str_image_base . 'leaf/left/leaf' . $index . '_a.gif" />' );
			echo( '<img src="' . $str_image_base . 'leaf/right/leaf' . $index . '_b.gif" />' );
			echo( '<img src="' . $str_image_base . 'leaf_alpha/left/leaf' . $index . '_a.gif" />' );
			echo( '<img src="' . $str_image_base . 'leaf_alpha/right/leaf' . $index . '_b.gif" />' );
		}

		echo( '</div>' );
	}
  
?>

  <input class="buttonback" type="button" value="&lt;&nbsp;&nbsp;Back to Generation History" onclick="openProblemDetail( <?= $g_int_problem_id ?> );" />
  <br /><br /><br />
  
  <div style="padding-bottom: 10px;">Generation:

    <select name="selectGeneration" id="selectGeneration" onchange="changeGeneration( this );">
    
<?php

	for ( $i = 1; $i < $g_int_number_of_generations + 1; ++$i )
	{
		echo( '<option value="' . $i . '"' );
	    
		if ( $i == $g_int_generation_id )
		{
			echo( ' selected="selected"' );
		}
		
		echo( '>' . $i . '</option>' );
	}

?>

    </select>
    
  </div>
  
  <table border="0" cellpadding="0" cellspacing="0" style="width: 650px; overflow: hidden; position: relative;">
    <tr>
      <td><div id="shelf" style="float: left;"></div></td>
      <td><div id="histogramA"></div><div id="histogramB"></div></td>
    </tr>
    
    <tr>
      <td>
        <form method="post" action="<?= htmlspecialchars( $_SERVER['REQUEST_URI'] ) ?>">
          <div style="float: left;">
            <div style="margin: 15px 0px 25px 0px;">
              <input class="buttoninput" type="button" name="Command" value="Select/Deselect Colored Plants" onclick="onSelectActivePlantClickHandler();" /><br /><br />
              <input class="buttoninput" type="submit" name="Command" value="Cross Highlighted Plants" onclick="return onCrossButtonClickHandler();"/><br /><br />
            </div>

            <h3>Highlighted Plant Information</h3>
            <br />
            <strong>Individual Plants</strong>
            <table class="listing" style="width: 320px; overflow: hidden;" id="SelectedPlantsTable">
              <tr>
                <th width="125">Generation</th>
                <th width="75">First Trait</th>
                <th width="75">Second Trait</th>
                <th>Remove</th>
              </tr>
              <tr>
                <td colspan="4">None</td>
              </tr>
            </table>
            <input class="buttoninput" type="button" name="Command" value="Remove All Highlighted Plants" onclick="onRemoveAllHighlightedPlantsClickHandler();" />
            <br /><br />
            <strong>All Plants</strong>
            <table class="listing" style="width: 320px; overflow: hidden;" id="selectedPlantsMeanAndVariance">
              <tr>
                <th width="125">Trait</th>
                <th width="75">Overall Mean</th>
                <th width="75">Overall SD</th>
              </tr>
              <tr>
                <td colspan="3">None</td>
              </tr>
            </table>
        
            <input type="hidden" name="SelectedPlants" id="SelectedPlants" value="" />
            <input type="hidden" name="SerialId" id="SerialId" value="<?= $g_str_serial ?>"/>
          </div>
        </form>
      </td>
      <td align="center">

        <table class="box" style="width: 265px">

          <tr>
            <th>Histogram Legend</th>
          </tr>
          <tr>
            <td>
              
              <table class="format" style="font-size: 0.85em;">
              
                <tr>
                  <td style="vertical-align: middle;" width="30"><div style="background-color: #9999ff; border: 1px solid #000000; width: 10px; height: 10px; font-size: 0.1em;"></div></td>
                  <td>First Trait, Deselected</td>                
                </tr>
                <tr>
                  <td style="vertical-align: middle;"><div style="background-color: #0000ff; border: 1px solid #000000; width: 10px; height: 10px; font-size: 0.1em;"></div></td>
                  <td>First Trait, Selected</td>                
                </tr>              
                <tr>
                  <td style="vertical-align: middle;"><div style="background-color: #ff9999; border: 1px solid #000000; width: 10px; height: 10px; font-size: 0.1em;"></div></td>
                  <td>Second Trait, Deselected</td>                
                </tr>
                <tr>
                  <td style="vertical-align: middle;"><div style="background-color: #ff0000; border: 1px solid #000000; width: 10px; height: 10px; font-size: 0.1em;"></div></td>
                  <td>Second Trait, Selected</td>                
                </tr>
              
              </table>
              
            </td>
          </tr>
        
        </table>

        <table class="box" style="width: 265px;">

          <tr>
            <th>Plant Shelf Legend</th>
          </tr>
          <tr>
            <td>
              
              <table class="format" style="font-size: 0.85em;">
                
                <tr>
                  <td>
                  
                    <img src="./images/leaf/left/leaf08_a.gif" alt="first trait" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;First Trait (Left)<div style="font-size: 7px;">&nbsp;</div>
                    <img src="./images/leaf/right/leaf08_b.gif" alt="second trait" />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Second Trait (Right)<div style="font-size: 18px;">&nbsp;</div>
            
<?php

$str_image_base = './images/leaf/';

for ( $i = 3; $i < 11; ++$i )
{
	$index = '00'.$i;
	$index = substr( $index, strlen( $index ) - 2 );
		    
	echo( '<img src="' . $str_image_base . 'left/leaf' . $index . '_a.gif" alt="' . $index . '" />' );
	echo( '<img src="' . $str_image_base . 'right/leaf' . $index . '_b.gif"  alt="' . $index . '" />&nbsp;' );
}

?>

                    <div>

                      <table class="format" style="width: 100%; font-size: 0.9em;">
                        <tr>
                          <td style="text-align: left;">Left</td>
                          <td style="text-align: center;"><strong>Histogram Position</strong></td>
                          <td style="text-align: right;">Right</td>
                        </tr>
                      </table>
                  
                    </div>
                  
                  </td>
                </tr>
              
              </table>
              
            </td>
          </tr>
        
        </table>

      </td>
    </tr>
  </table>
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
	global $g_obj_db, $g_obj_user, $g_obj_problem_manager, $g_obj_generation_manager, $g_int_problem_id, $g_arr_problem_info, $g_int_number_of_generations;
	
	$res_problem = $g_obj_problem_manager->view_problem_details( $g_int_problem_id );
	
	if ( $g_obj_db->get_number_of_rows( $res_problem ) == 0 )
	{
		(new MessageHandler) ->  add_message( MSG_ERROR, "The Problem does not exist" );	
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
	
	$str_plants = "";

	// get all the generations
	for ( $i = 1; $i < $g_int_number_of_generations + 1; ++$i )
	{
		$res_generation = $g_obj_generation_manager->get_generation( $g_obj_user->str_username, $g_int_problem_id, $i );
		
		$str_plants = $str_plants . ' objPlants[' . $i . '] = new Array(';
		
		for ( $j = 0; $j < $g_obj_db->get_number_of_rows( $res_generation ); ++$j )
		{
			$res_plant = $g_obj_db->fetch( $res_generation );
			
			$str_plants = $str_plants . 'new xgene360_plant( \'' . $res_plant->plant_id . '\', ' . $res_plant->value_trait_A . ', ' . $res_plant->value_trait_B . ' )';
			
			if ( $j != $g_obj_db->get_number_of_rows( $res_generation ) - 1 )
			{
				$str_plants = $str_plants . ", ";
			}
		}
		
		$str_plants = $str_plants . ');' . "\n";
	}
	
	$g_str_script_block = "
 
	xgene360_cu.using( 'sortabletable' );
	
	var objPlants = new Array();
	
	" . $str_plants;

	// set trait a and trait b info
	$g_str_script_block = $g_str_script_block . 'xgene360_plantshelf.strTraitAName = \'' . $g_arr_problem_info->trait_A_name . ' (' . $g_arr_problem_info->trait_A_unit . ')\';' . "\n";
	$g_str_script_block = $g_str_script_block . 'xgene360_plantshelf.strTraitBName = \'' . $g_arr_problem_info->trait_B_name . ' (' . $g_arr_problem_info->trait_B_unit . ')\';' . "\n";
	
	$g_str_script_block = $g_str_script_block . 'objHistogramA.assignTraitName( \'' . $g_arr_problem_info->trait_A_name . ' (' . $g_arr_problem_info->trait_A_unit . ')\');' . "\n";
	$g_str_script_block = $g_str_script_block . 'objHistogramB.assignTraitName( \'' . $g_arr_problem_info->trait_B_name . ' (' . $g_arr_problem_info->trait_B_unit . ')\');' . "\n";
	
	// calculate the fixed range
	$dbl_range = ( $g_arr_problem_info->trait_A_histogram_range + $g_arr_problem_info->trait_A_var ) / 100;
	$dbl_trait_min = min( $g_arr_problem_info->trait_A_parent_A_mean, $g_arr_problem_info->trait_A_parent_B_mean );
	$dbl_trait_min = $dbl_trait_min - $dbl_trait_min * $dbl_range;
	
	$dbl_trait_max = max( $g_arr_problem_info->trait_A_parent_A_mean, $g_arr_problem_info->trait_A_parent_B_mean );
	$dbl_trait_max = $dbl_trait_max + $dbl_trait_max * $dbl_range;

	$g_str_script_block = $g_str_script_block . 'objHistogramA.setRange( ' . $dbl_trait_min . ', ' . $dbl_trait_max . ' );' . "\n";
	
	$dbl_range = ( $g_arr_problem_info->trait_B_histogram_range + $g_arr_problem_info->trait_B_var ) / 100;
	$dbl_trait_min = min( $g_arr_problem_info->trait_B_parent_A_mean, $g_arr_problem_info->trait_B_parent_B_mean );
	$dbl_trait_min = $dbl_trait_min - $dbl_trait_min * $dbl_range;
	
	$dbl_trait_max = max( $g_arr_problem_info->trait_B_parent_A_mean, $g_arr_problem_info->trait_B_parent_B_mean );
	$dbl_trait_max = $dbl_trait_max + $dbl_trait_max * $dbl_range;
	
	$g_str_script_block = $g_str_script_block . 'objHistogramB.setRange( ' . $dbl_trait_min . ', ' . $dbl_trait_max . ' );' . "\n";
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
	
	if ( isset( $_POST['Command'] ) && $g_obj_lock->page_lock( $pageHandler -> get_post_value( 'SerialId' ) ) )
	{
		$command = $_POST['Command'];
	  
		switch ( $command )
		{
			case 'Cross Highlighted Plants':
			{
				on_cross_handler();
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
*    Purpose:           Process crossing the plants
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_cross_handler()
{
	global $g_obj_problem_manager, $g_obj_generation_manager, $g_int_problem_id, $g_int_generation_id, $g_arr_problem_info, $g_obj_db, $g_obj_user, $g_int_number_of_generations;
	
	$str_selected_plants = $pageHandler -> get_post_value( 'SelectedPlants' );
	
	if ( empty( $str_selected_plants ) )
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, 'Please select at least one plant' );
	}
	
	else if ( $g_int_number_of_generations + 1 > $g_arr_problem_info->max_cross )
	{
		(new MessageHandler) ->  add_message( MSG_FAIL, 'You have reached the maximum number of generations' );
	}
	
	else
	{
		$arr_plant_ids = explode( ';', $str_selected_plants );
		
		// save the parents for later use
		$g_obj_generation_manager->set_parents( $g_int_problem_id, $arr_plant_ids );
		
		// retrieve the data regarding the parents
		$arr_parents_genotypes = $g_obj_generation_manager->get_parents_genotypes( $g_int_problem_id, $arr_plant_ids );
		
		// create all parent plants
		$arr_parent_plants = array();
		
		for ( $i = 0; $i < count( $arr_parents_genotypes ); ++$i )
		{
			$obj_plant = new Plant;
			$obj_plant->arr_gene[0] = new Gene;
			$obj_plant->arr_gene[1] = new Gene;
			
			$obj_plant->arr_gene[0]->decrypt( $arr_parents_genotypes[$i][0] );
			$obj_plant->arr_gene[1]->decrypt( $arr_parents_genotypes[$i][1] );
			
			array_push( $arr_parent_plants, $obj_plant );
		}
		
		$arr_new_generation = Simulation::cross_plants( $arr_parent_plants, $g_arr_problem_info->number_of_displayed_plants );
		
		$obj_trait_A = $g_obj_problem_manager->create_trait( $g_arr_problem_info->trait_A_name, $g_arr_problem_info->trait_A_number_of_genes, 
																$g_arr_problem_info->trait_A_parent_A_mean, $g_arr_problem_info->trait_A_parent_B_mean, $g_arr_problem_info->trait_A_var );
		$obj_trait_B = $g_obj_problem_manager->create_trait( $g_arr_problem_info->trait_B_name, $g_arr_problem_info->trait_B_number_of_genes, 
																$g_arr_problem_info->trait_B_parent_A_mean, $g_arr_problem_info->trait_B_parent_B_mean, $g_arr_problem_info->trait_B_var );
		
		
		$res_number_of_generations = $g_obj_generation_manager->get_number_of_generations( $g_obj_user->str_username, $g_int_problem_id );
		$res_row = $g_obj_db->fetch( $res_number_of_generations );
		
		$int_generation_num = $res_row->generation_count + 1;
		
		$g_obj_generation_manager->set_array_generation( $g_int_problem_id, $obj_trait_A, $obj_trait_B, $arr_new_generation, $int_generation_num );
		
		$g_int_generation_id = $int_generation_num;
		$g_int_number_of_generations = $int_generation_num;
	}
}

?>
