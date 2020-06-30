<?php

/*
* include necessary files
*/

require_once( '../includes/global.inc.php' );
require_once( '../includes/classes/db/coursemanager.class.php' );
require_once( '../includes/classes/db/generationmanager.class.php' );
require_once( '../includes/classes/db/problemmanager.class.php' );

/*
* necessary id
*/

$g_str_parent_page = './manageproblems.php';

/*
* initialize common stuff
*/

$g_obj_db = null;
$g_obj_lock = null;
$g_str_serial = null;
$g_obj_user = null;

PageHandler::initialize();
PageHandler::check_permission( array( UP_ADMINISTRATOR, UP_PROFESSOR ) );

$g_obj_course_manager = new CourseManager( $g_obj_user, $g_obj_db );
$g_obj_problem_manager = new ProblemManager( $g_obj_user, $g_obj_db );

/*
* required info
*/

process_post();

/*
* set header stuff
*/

$g_str_page_title = "Manage Problems &gt; Create Problem";
$g_arr_nav_links = $g_arr_nav_defined_links[$g_obj_user->int_privilege];
$g_arr_scripts = array( 'createproblem.js' );

require_once( '../includes/header.inc.php' );

get_problem_values();

if ( $g_bln_display_content )
{
?>

<!-- Start Content -->
<div>

  <form method="post" id="CreateProblemForm" action="<?= $_SERVER['REQUEST_URI'] ?>">

    <input class="buttonback" type="button" value="&lt;&nbsp;&nbsp;Back to Problem Listing" onclick="window.location='<?= $g_str_parent_page ?>';" />
    <br /><br /><br />

    <table class="format">
      
      <tr>
        <td>
          <input class="buttoninput" type="submit" value="Create New Problem" name="Command" onclick="return validateCreateProblemForm();"/>&nbsp;
          <input class="buttoninput" type="reset" value="Reset" name="Command" />
        </td>
      </tr>

      <tr>
        <td>      
          
          <table class="box" style="width: 100%">
            
            <tr>
              <th>Create Problem</th>
            </tr>
            
            <tr>
              <td>
                
                <table>
                  <tr>
                    <td width="170">Copy Problem From:</td>
                    <td>
                      <select id="CopyProblem" name="CopyProblem">

<?php

echo( '<option selected="selected" value="">&nbsp;Select a problem to copy</option>' );         
echo( '<option value="">&nbsp;----------</option>' );   
echo( '<option value="">&nbsp;</option>' );

$res_problems = $g_obj_problem_manager->view_problems();

for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $res_problems ); ++$i )
{
	$res_row = $g_obj_db->fetch( $res_problems );
	
	echo( '<option value="' . htmlspecialchars( $res_row->problem_id ) . '">&nbsp;' . htmlspecialchars( $res_row->problem_name ) . '&nbsp;</option>' . "\n" );
}

?>

                      </select>&nbsp;
                      <input class="buttoninput" type="submit" name="Command" value="Copy" onclick="return validateCopyProblem();" />
                    </td>
                  </tr>
                  <tr>
                    <td width="170">Problem Name:</td>
                    <td><input class="longtextinput" type="text" name="ProblemName" id="ProblemName" value="<?= htmlspecialchars( $g_str_problem_name ) ?>" /></td>
                  </tr>
                  <tr>
                    <td style="vertical-align: top;">Problem Description:</td>
                    <td><textarea class="textareainput" name="ProblemDescription" id="ProblemDescription" cols="60" rows="10"><?= htmlspecialchars( $g_str_problem_description ) ?></textarea>
                  </tr>
                  <tr>
                    <td>Course:</td>
                    <td>
                      <select name="CourseId" id="CourseId">

<?php

$res_courses = $g_obj_course_manager->view_courses();

for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $res_courses ); ++$i )
{
	$res_row = $g_obj_db->fetch( $res_courses );

	echo( '<option value="' . $res_row->CourseId . '">' . $res_row->Name . '</option>' . "\n" );
}
    
?>

                      </select>
                    </td>
                  </tr>
                  <!--
                  <tr>
                    <td colspan="2" height="10">&nbsp;</td>
                  </tr>
                  <tr>
                    <td>Start Date:</td>
                    <td>
<?= PageHandler::generate_month( 'StartDateMonth', $g_int_start_date_month ); ?>

                    &nbsp;

<?= PageHandler::generate_day( 'StartDateDay', $g_int_start_date_day ); ?>

                    &nbsp;
                    
<?= PageHandler::generate_year( 'StartDateYear', date( 'Y' ), $g_int_start_date_year ); ?>

                    &nbsp;&nbsp;&nbsp;

<?= PageHandler::generate_hour( 'StartDateHour', $g_int_start_date_hour ); ?>

                    :
                  
<?= PageHandler::generate_minute( 'StartDateMinute', $g_int_start_date_minute ); ?>

                    </td>
                  </tr>
                  <tr>
                    <td>Due Date:</td>
                    <td>

<?= PageHandler::generate_month( 'DueDateMonth', $g_int_due_date_month ); ?>

                    &nbsp;

<?= PageHandler::generate_day( 'DueDateDay', $g_int_due_date_day ); ?>

                    &nbsp;

<?= PageHandler::generate_year( 'DueDateYear', date( 'Y' ), $g_int_due_date_year ); ?>

                    &nbsp;&nbsp;&nbsp;
                  
<?= PageHandler::generate_hour( 'DueDateHour', $g_int_due_date_hour ); ?>

                    :
                  
<?= PageHandler::generate_minute( 'DueDateMinute', $g_int_due_date_minute ); ?>

                    </td>
                  </tr>
-->
                  <tr>
                    <td colspan="2" height="10">&nbsp;</td>
                  </tr>
                  <tr>
                    <td>First Trait Name:</td>
                    <td><input class="longtextinput" type="text" name="TraitAName" id="TraitAName" value="<?= htmlspecialchars( $g_str_trait_A_name ) ?>" onkeyup="livePreview( 'TraitAName', 'TraitAPreview', 'Trait - ', 'First Trait' );" /></td>
                  </tr>
                  <tr>
                    <td>Second Trait Name:</td>
                    <td><input class="longtextinput" type="text" name="TraitBName" id="TraitBName" value="<?= htmlspecialchars( $g_str_trait_B_name ) ?>" onkeyup="livePreview( 'TraitBName', 'TraitBPreview', 'Trait - ', 'Second Trait' );" /></td>
                  </tr>     
                  <tr>
                    <td colspan="2" height="15">&nbsp;</td>
                  </tr>                               
                  <tr>
                    <td>Max # of Crosses:</td>
                    <td><input class="numberinput" type="text" name="MaxCross" id="MaxCross" value="<?= htmlspecialchars( $g_int_max_cross ) ?>" />&nbsp;</td>
                  </tr>
                  <tr>
                    <td>Offspring per Cross:</td>
                    <td><input class="numberinput" type="text" name="OffspringPerCross" id="OffspringPerCross" value="<?= htmlspecialchars( $g_int_offspring_per_cross ) ?>" />&nbsp;plants</td>
                  </tr>
                  <tr>
                    <td>Displayed Plants per Cross:</td>
                    <td><input class="numberinput" type="text" name="PlantsDisplayed" id="PlantsDisplayed" value="<?= htmlspecialchars( $g_int_plants_displayed ) ?>" />&nbsp;plants</td>
                  </tr>
                  <tr>
                    <td>Range of Acceptance:<div style="font-size: 0.8em;">(for auto-submitted solutions)</div></td>
                    <td><input class="numberinput" type="text" name="RangeOfAcceptance" id="RangeOfAcceptance" value="<?= htmlspecialchars( $g_dbl_range_of_acceptance ) ?>" />&nbsp;%</td>
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
                    <td colspan="2"><h3 id="TraitAPreview">First Trait</h3></td>
                    <td width="100">&nbsp;</td>
                    <td colspan="2"><h3 id="TraitBPreview">Second Trait</h3></td>
                    <td width="50">&nbsp;</td>
                  </tr>

                  <tr>
                    <td width="150">Parent 1 Mean:</td>
                    <td><input class="numberinput" type="text" name="TraitAParent1Mean" id="TraitAParent1Mean" value="<?= htmlspecialchars( $g_dbl_trait_A_parent_1_mean ) ?>" /></td>
                    <td>&nbsp;</td>
                    <td width="150">Parent 1 Mean:</td>
                    <td><input class="numberinput" type="text" name="TraitBParent1Mean" id="TraitBParent1Mean" value="<?= htmlspecialchars( $g_dbl_trait_B_parent_1_mean ) ?>" /></td>
                    <td>&nbsp;</td>
                  </tr>

                  <tr>
                    <td>Parent 2 Mean:</td>
                    <td><input class="numberinput" type="text" name="TraitAParent2Mean" id="TraitAParent2Mean" value="<?= htmlspecialchars( $g_dbl_trait_A_parent_2_mean ) ?>" /></td>
                    <td>&nbsp;</td>
                    <td>Parent 2 Mean:</td>
                    <td><input class="numberinput" type="text" name="TraitBParent2Mean" id="TraitBParent2Mean" value="<?= htmlspecialchars( $g_dbl_trait_B_parent_2_mean ) ?>" /></td>
                    <td>&nbsp;</td>
                  </tr>

                  <tr>
                    <td>Environmental Variance:</td>
                    <td><input class="numberinput" type="text" name="TraitAVariance" id="TraitAVariance" value="<?= htmlspecialchars( $g_dbl_trait_A_variance ) ?>" /> %</td>
                    <td>&nbsp;</td>
                    <td>Environmental Variance:</td>
                    <td><input class="numberinput" type="text" name="TraitBVariance" id="TraitBVariance" value="<?= htmlspecialchars( $g_dbl_trait_B_variance ) ?>" /> %</td>
                    <td>&nbsp;</td>
                  </tr>

                  <tr>
                    <td>Number of Genes:</td>
                    <td><input class="numberinput" type="text" name="TraitANumberOfGenes" id="TraitANumberOfGenes" value="<?= htmlspecialchars( $g_int_trait_A_number_of_genes ) ?>" /></td>
                    <td>&nbsp;</td>
                    <td>Number of Genes:</td>
                    <td><input class="numberinput" type="text" name="TraitBNumberOfGenes" id="TraitBNumberOfGenes" value="<?= htmlspecialchars( $g_int_trait_B_number_of_genes ) ?>" /></td>
                    <td>&nbsp;</td>
                  </tr>

                  <tr>
                    <td>Unit:</td>
                    <td><input class="textinput" type="text" name="TraitAUnit" id="TraitAUnit" value="<?= htmlspecialchars( $g_str_trait_A_unit ) ?>" /></td>
                    <td>&nbsp;</td>
                    <td>Unit:</td>
                    <td><input class="textinput" type="text" name="TraitBUnit" id="TraitBUnit" value="<?= htmlspecialchars( $g_str_trait_B_unit ) ?>" /></td>
                    <td>&nbsp;</td>
                  </tr>

                  <tr>
                    <td>Histogram Range:</td>
                    <td><input class="textinput" type="text" name="HistogramRangeA" id="HistogramRangeA" value="<?= htmlspecialchars( $g_int_histogram_A_range ) ?>" />&nbsp;%</td>
                    <td>&nbsp;</td>
                    <td>Histogram Range:</td>
                    <td><input class="textinput" type="text" name="HistogramRangeB" id="HistogramRangeB" value="<?= htmlspecialchars( $g_int_histogram_B_range ) ?>" />&nbsp;%</td>
                    <td>&nbsp;</td>
                  </tr>

                </table>

              </td>
            </tr>

          </table>

        </td>
      </tr>

      <tr>
        <td>
          <input type="hidden" name="StartDate" id="StartDate" value="" />
          <input type="hidden" name="DueDate" id="DueDate" value="" />
          <input class="buttoninput" type="submit" value="Create New Problem" name="Command" onclick="return validateCreateProblemForm();"/>&nbsp;
          <input class="buttoninput" type="reset" value="Reset" name="Command" />
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

/**  Function: void get_problem_values()
*    ---------------------------------------------------------------- 
*    Purpose:           Get the problem values from either db or POST
*    Arguments:         None
*                       
*    Returns/Assigns:   Sets all variables regarding problem info
*                       variable is read from db if the user is
*                       trying to copy the problem or POST if
*                       the user fails to provide all necessary
*                       information
*/
function get_problem_values()
{
	global $g_str_problem_name, $g_str_problem_description, $g_int_start_date_month, $g_int_start_date_day,
			$g_int_start_date_year, $g_int_start_date_hour, $g_int_start_date_minute, $g_int_due_date_month,
			$g_int_due_date_day, $g_int_due_date_year, $g_int_due_date_hour, $g_int_due_date_minute,
			$g_str_trait_A_name, $g_str_trait_B_name, $g_int_max_cross, $g_int_offspring_per_cross,
			$g_int_plants_displayed, $g_dbl_range_of_acceptance, $g_dbl_trait_A_parent_1_mean, $g_dbl_trait_A_parent_2_mean,
			$g_dbl_trait_B_parent_1_mean, $g_dbl_trait_B_parent_2_mean, $g_dbl_trait_A_variance, $g_dbl_trait_B_variance,
			$g_dbl_trait_A_heritability, $g_dbl_trait_B_heritability, $g_int_trait_A_number_of_genes, $g_int_trait_B_number_of_genes,
			$g_str_trait_A_unit, $g_str_trait_B_unit, $g_bln_fail, $g_arr_problem_info, $g_int_histogram_A_range, $g_int_histogram_B_range;
	
	if ( !!isset( $g_arr_problem_info ) )
	{
		$g_str_problem_name = $g_arr_problem_info->problem_name;
		$g_str_problem_description = $g_arr_problem_info->problem_desc;
		$g_int_start_date_month = date( 'n', $g_arr_problem_info->start_date );
		$g_int_start_date_day = date( 'j', $g_arr_problem_info->start_date );
		$g_int_start_date_year = date( 'Y', $g_arr_problem_info->start_date );
		$g_int_start_date_hour = date( 'H', $g_arr_problem_info->start_date );
		$g_int_start_date_minute = date( 'i', $g_arr_problem_info->start_date );
		$g_int_due_date_month = date( 'n', $g_arr_problem_info->due_date );
		$g_int_due_date_day = date( 'j', $g_arr_problem_info->due_date );
		$g_int_due_date_year = date( 'Y', $g_arr_problem_info->due_date );
		$g_int_due_date_hour = date( 'H', $g_arr_problem_info->due_date );
		$g_int_due_date_minute = date( 'i', $g_arr_problem_info->due_date );
		$g_str_trait_A_name = $g_arr_problem_info->trait_A_name;
		$g_str_trait_B_name = $g_arr_problem_info->trait_B_name;
		$g_int_max_cross = $g_arr_problem_info->max_cross;
		$g_int_offspring_per_cross = $g_arr_problem_info->number_of_progeny_per_cross;
		$g_int_plants_displayed = $g_arr_problem_info->number_of_displayed_plants;
		$g_dbl_range_of_acceptance = $g_arr_problem_info->range_of_acceptance;
		$g_dbl_trait_A_parent_1_mean = $g_arr_problem_info->trait_A_parent_A_mean;
		$g_dbl_trait_A_parent_2_mean = $g_arr_problem_info->trait_A_parent_B_mean;
		$g_dbl_trait_B_parent_1_mean = $g_arr_problem_info->trait_B_parent_A_mean;
		$g_dbl_trait_B_parent_2_mean = $g_arr_problem_info->trait_B_parent_B_mean;
		$g_dbl_trait_A_variance = $g_arr_problem_info->trait_A_var;
		$g_dbl_trait_B_variance = $g_arr_problem_info->trait_B_var;
		$g_dbl_trait_A_heritability = $g_arr_problem_info->trait_A_h2;
		$g_dbl_trait_B_heritability = $g_arr_problem_info->trait_B_h2;
		$g_int_trait_A_number_of_genes = $g_arr_problem_info->trait_A_number_of_genes;
		$g_int_trait_B_number_of_genes = $g_arr_problem_info->trait_B_number_of_genes;
		$g_str_trait_A_unit = $g_arr_problem_info->trait_A_unit;
		$g_str_trait_B_unit = $g_arr_problem_info->trait_B_unit;
		$g_int_histogram_A_range = $g_arr_problem_info->trait_A_histogram_range;
		$g_int_histogram_B_range = $g_arr_problem_info->trait_B_histogram_range;
	}
	
	else
	{
		$g_str_problem_name = PageHandler::write_post_value_if_failed( 'ProblemName' );
		$g_str_problem_description = PageHandler::write_post_value_if_failed( 'ProblemDescription' );
		$g_int_start_date_month = $g_bln_fail ? PageHandler::write_post_value_if_failed( 'StartDateMonth' ) : date( 'n' );
		$g_int_start_date_day = $g_bln_fail ? PageHandler::write_post_value_if_failed( 'StartDateDay' ) : date( 'j' );
		$g_int_start_date_year = $g_bln_fail ? PageHandler::write_post_value_if_failed( 'StartDateYear' ) : date( 'Y' );
		$g_int_start_date_hour = $g_bln_fail ? PageHandler::write_post_value_if_failed( 'StartDateHour' ) : 0;
		$g_int_start_date_minute = $g_bln_fail ? PageHandler::write_post_value_if_failed( 'StartDateMinute' ) : 0;
		$g_int_due_date_month = $g_bln_fail ? PageHandler::write_post_value_if_failed( 'DueDateMonth' ) : date( 'n' );
		$g_int_due_date_day = $g_bln_fail ? PageHandler::write_post_value_if_failed( 'DueDateDay' ) : date( 'j' ) + 1;
		$g_int_due_date_year = $g_bln_fail ? PageHandler::write_post_value_if_failed( 'DueDateYear' ) : date( 'Y' );
		$g_int_due_date_hour = $g_bln_fail ? PageHandler::write_post_value_if_failed( 'DueDateHour' ) : 0;
		$g_int_due_date_minute = $g_bln_fail ? PageHandler::write_post_value_if_failed( 'DueDateMinute' ) : 0;
		$g_str_trait_A_name = PageHandler::write_post_value_if_failed( 'TraitAName' );
		$g_str_trait_B_name = PageHandler::write_post_value_if_failed( 'TraitBName' );
		$g_int_max_cross = PageHandler::write_post_value_if_failed( 'MaxCross' );
		$g_int_offspring_per_cross = PageHandler::write_post_value_if_failed( 'OffspringPerCross' );
		$g_int_plants_displayed = PageHandler::write_post_value_if_failed( 'PlantsDisplayed' );
		$g_dbl_range_of_acceptance = PageHandler::write_post_value_if_failed( 'RangeOfAcceptance' );
		$g_dbl_trait_A_parent_1_mean = PageHandler::write_post_value_if_failed( 'TraitAParent1Mean' );
		$g_dbl_trait_A_parent_2_mean = PageHandler::write_post_value_if_failed( 'TraitAParent2Mean' );
		$g_dbl_trait_B_parent_1_mean = PageHandler::write_post_value_if_failed( 'TraitBParent1Mean' );
		$g_dbl_trait_B_parent_2_mean = PageHandler::write_post_value_if_failed( 'TraitBParent2Mean' );
		$g_dbl_trait_A_variance = PageHandler::write_post_value_if_failed( 'TraitAVariance' );
		$g_dbl_trait_B_variance = PageHandler::write_post_value_if_failed( 'TraitBVariance' );
		$g_int_trait_A_number_of_genes = PageHandler::write_post_value_if_failed( 'TraitANumberOfGenes' );
		$g_int_trait_B_number_of_genes = PageHandler::write_post_value_if_failed( 'TraitBNumberOfGenes' );
		$g_str_trait_A_unit = PageHandler::write_post_value_if_failed( 'TraitAUnit' );
		$g_str_trait_B_unit = PageHandler::write_post_value_if_failed( 'TraitBUnit' );
		$g_int_histogram_A_range = $g_bln_fail ? PageHandler::write_post_value_if_failed( 'HistogramRangeA' ) : 5;
		$g_int_histogram_B_range = $g_bln_fail ? PageHandler::write_post_value_if_failed( 'HistogramRangeB' ) : 5;
	}
}

/**  Function: void process_post()
*    ---------------------------------------------------------------- 
*    Purpose:           Call appropriate functions based on the POST
*                       command
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*                       
*/
function process_post()
{
	global $g_obj_lock;
	
	if ( isset( $_POST['Command'] ) && $g_obj_lock->page_lock( PageHandler::get_post_value( 'SerialId' ) ) )
	{
		$str_command = $_POST['Command'];
		
		switch ( $str_command )
		{
			case 'Create New Problem':
			{
				on_create_handler();
			}
			break;
			
			case 'Copy':
			{
				on_copy_handler();
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
*    Purpose:           Process creating the problem
*    Arguments:         None
*                       
*    Returns/Assigns:   None
*/
function on_create_handler()
{
	global $g_int_problem_id, $g_obj_problem_manager;
	
	$str_problem_name = PageHandler::get_post_value( 'ProblemName' );
	$str_problem_description = PageHandler::get_post_value( 'ProblemDescription' );
	$int_course_id = PageHandler::get_post_value( 'CourseId' );
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
	$int_max_cross = PageHandler::get_post_Value( 'MaxCross' );
	$int_plants_displayed = PageHandler::get_post_value( 'PlantsDisplayed' );
	$dbl_range_of_acceptance = PageHandler::get_post_value( 'RangeOfAcceptance' );
	$dat_start_date = PageHandler::get_post_value( 'StartDate' );
	$date_due_date  = PageHandler::get_post_value( 'DueDate' );
	
	if ( !isset( $str_problem_name ) || !isset( $str_problem_description ) || !isset( $int_course_id ) ||
			!isset( $str_Trait_A_name ) || !isset( $str_Trait_B_name ) || !isset( $str_Trait_A_unit ) || !isset( $str_Trait_B_unit ) ||
			!isset( $int_Trait_A_number_of_genes ) || !isset( $int_Trait_B_number_of_genes ) ||
			!isset( $dbl_Trait_A_variance ) || !isset( $dbl_Trait_B_variance ) || 
			!isset( $dbl_Trait_A_Parent_A_Mean ) || !isset( $dbl_Trait_A_Parent_B_Mean ) || 
			!isset( $dbl_Trait_B_Parent_A_Mean ) || !isset( $dbl_Trait_B_Parent_B_Mean ) ||
			!isset( $int_Histogram_A_Range ) || !isset( $int_Histogram_B_Range ) ||
			!isset( $int_offspring_per_cross ) || !isset( $int_max_cross )  || !isset( $int_plants_displayed ) ||
			!isset( $dbl_range_of_acceptance ))
	{
		MessageHandler::add_message( MSG_FAIL, 'Please enter the necessary information' );
		return;
	}
	
	if ( $g_obj_problem_manager->add_problem( $str_problem_name, $str_problem_description, $int_course_id,
											  $str_Trait_A_name, $str_Trait_B_name, $str_Trait_A_unit, $str_Trait_B_unit,
											  $int_Trait_A_number_of_genes, $int_Trait_B_number_of_genes, $dbl_Trait_A_variance,
											  $dbl_Trait_B_variance, $dbl_Trait_A_Parent_A_Mean, $dbl_Trait_A_Parent_B_Mean,
											  $dbl_Trait_B_Parent_A_Mean, $dbl_Trait_B_Parent_B_Mean, $int_offspring_per_cross,
											  $int_max_cross, $int_plants_displayed, $dbl_range_of_acceptance,
											  $int_Histogram_A_Range, $int_Histogram_B_Range, $dat_start_date, $date_due_date ) )

	{
		MessageHandler::add_message( MSG_SUCCESS, 'Successfully created the Problem "' . $str_problem_name . '"' );
	}
	
	else
	{
		MessageHandler::add_message( MSG_FAIL, 'Failed to create the Problem "' . $str_problem_name . '"' );
	}
}

/**  Function: void on_copy_handler()
*    ---------------------------------------------------------------- 
*    Purpose:           Process copying the problem
*    Arguments:         None
*                       
*    Returns/Assigns:   None        
*/
function on_copy_handler()
{
	global $g_obj_db, $g_obj_problem_manager, $g_arr_problem_info;
	
	$int_problem_id = PageHandler::get_post_value( 'CopyProblem' );
	
	if ( !isset( $int_problem_id ) )
	{
		MessageHandler::add_message( MSG_FAIL, 'Please select a problem' );
		return;
	}
		
	$res_problem = $g_obj_problem_manager->view_problem_details( $int_problem_id );
	
	if ( $g_obj_db->get_number_of_rows( $res_problem ) == 0 )
	{
		MessageHandler::add_message( MSG_FAIL, 'The problem does not exist' );
		return;
	}
	
	else
	{
		$g_arr_problem_info = $g_obj_db->fetch( $res_problem );
	}
}

?>
