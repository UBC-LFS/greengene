<?php

/*
* include necessary files
*/

require_once( '../includes/global.inc.php' );
require_once( '../includes/classes/db/solutionmanager.class.php' );
require_once( '../includes/classes/db/problemmanager.class.php' );

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

$g_obj_solution_manager = new SolutionManager( $g_obj_user, $g_obj_db );
$g_obj_problem_manager = new ProblemManager( $g_obj_user, $g_obj_db );

/*
* required info
*/

$g_int_problem_id = $_GET["ProblemId"];

verify_problem_exists();

/*
* set header stuff
*/

$g_str_page_title = "View Student Solutions";
$g_arr_nav_links = $g_arr_nav_defined_links[$g_obj_user->int_privilege];
$g_arr_scripts = array( 'commonadmin.js' );

require_once( '../includes/header.inc.php' );

if ( $g_bln_display_content )
{
?>

<!-- Start Content -->

<table class="box">
  
  <tr>
    <th>Problem Solutions</th>
  </tr>
  
  <tr>
    <td>
    
      <table class="format">

        <tr>
          <td width="165">&nbsp;</td>
          <td width="75"><h4><?= $g_arr_problem_info->trait_A_name ?></h4></td>
          <td width="30">&nbsp;</td>
          <td width="100"><h4><?= $g_arr_problem_info->trait_B_name ?></h4></td>
        </tr>
        
        <tr>
          <td><strong>Parent 1 Mean:</strong></td>
          <td><?= $g_arr_problem_info->trait_A_parent_A_mean ?></td>
          <td>&nbsp;</td>
          <td><?= $g_arr_problem_info->trait_B_parent_A_mean ?></td>
        </tr>
        
        <tr>
          <td><strong>Parent 2 Mean:</strong></td>
          <td><?= $g_arr_problem_info->trait_A_parent_B_mean ?></td>
          <td>&nbsp;</td>
          <td><?= $g_arr_problem_info->trait_B_parent_B_mean ?></td>
        </tr>
        
        <tr>
          <td><strong>Enviromental Variance:&nbsp;</strong></td>
          <td><?= $g_arr_problem_info->trait_A_var ?></td>
          <td>&nbsp;</td>
          <td><?= $g_arr_problem_info->trait_B_var ?></td>
        </tr>
        
        <tr>
          <td><strong>h<sup>2</sup>:&nbsp;</strong></td>
          <td><?= $g_arr_problem_info->trait_A_h2 ?></td>
          <td>&nbsp;</td>
          <td><?= $g_arr_problem_info->trait_B_h2 ?></td>
        </tr>
        
        <tr>
          <td><strong>Number of Genes:</strong></td>
          <td><?= $g_arr_problem_info->trait_A_number_of_genes ?></td>
          <td>&nbsp;</td>
          <td><?= $g_arr_problem_info->trait_B_number_of_genes ?></td>
        </tr>

        <tr>
          <td colspan="4" height="8"></td>
        </tr>
        
        <tr>
          <td>Range of Acceptance:<div style="font-size: 0.8em;">(for auto-submitted solutions)</div></td>
          <td colspan="3"><?= $g_arr_problem_info->range_of_acceptance ?>%</td>
        </tr>
        
      </table>
    
    </td>
  </tr>

</table>

<p>&nbsp;</p>

<table class="listing" id="listOfSolutions">
  
  <tr>
    <th width="85">CWL Username</th>
    <th width="100">First Name</th>
    <th width="100">Last Name</th>
    <th width="125">&nbsp;</th>
    <th width="75">Mean 1</th>
    <th width="75">Mean 2</th>
    <th width="75">Var</th>
    <th width="75" style="text-transform: none;">h<sup>2</sup></th>
    <th width="75"># Genes</th>
    <th width="25">&nbsp;</th>        
    <th>Handin Date</th>
  </tr>

<?php

$g_dbl_range_of_acceptance = $g_arr_problem_info->range_of_acceptance;

$g_res_student_solutions = $g_obj_solution_manager->view_student_solutions( $g_int_problem_id );

if ( $g_obj_db->get_number_of_rows( $g_res_student_solutions ) == 0 )
{
	echo( '<tr>' . "\n" );
	echo( '<td colspan="11">There is no solution submitted yet</td>' . "\n" );
	echo( '</tr>' . "\n" );
}

for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $g_res_student_solutions ); ++$i )
{
	$res_solution = $g_obj_db->fetch( $g_res_student_solutions );
	
	echo( '<tr>' . "\n" );
	echo( '<td style="border-bottom: 0px;">' . htmlspecialchars( $res_solution->UserId ) . '</td>' . "\n" );
	echo( '<td style="border-bottom: 0px;">' . htmlspecialchars( $res_solution->FirstName ) . '</td>' . "\n" );
	echo( '<td style="border-bottom: 0px;">' . htmlspecialchars( $res_solution->LastName ) . '</td>' . "\n" );
	echo( '<td style="border-bottom: 0px;"><strong>FIRST TRAIT</strong></td>' . "\n" );
	echo( '<td style="border-bottom: 0px;">' . compare_result( $g_arr_problem_info->trait_A_parent_A_mean, $res_solution->trait_A_parent_A_mean ) . '</td>' . "\n" );
	echo( '<td style="border-bottom: 0px;">' . compare_result( $g_arr_problem_info->trait_A_parent_B_mean, $res_solution->trait_A_parent_B_mean ) . '</td>' . "\n" );
	echo( '<td style="border-bottom: 0px;">' . compare_result( $g_arr_problem_info->trait_A_var, $res_solution->trait_A_var ) . '</td>' . "\n" );
	echo( '<td style="border-bottom: 0px;">' . compare_result( $g_arr_problem_info->trait_A_h2, $res_solution->trait_A_h2 ) . '</td>' . "\n" );
	echo( '<td style="border-bottom: 0px;">' . compare_result( $g_arr_problem_info->trait_A_number_of_genes, $res_solution->trait_A_number_of_genes ) . '</td>' . "\n" );
	echo( '<td style="border-bottom: 0px;">&nbsp;</td>' . "\n" );
	echo( '<td style="border-bottom: 0px;">' . htmlspecialchars( PageHandler::format_date( $res_solution->hand_in_date ) ) . '</td>' . "\n" );
	echo( '</tr>' . "\n" );
	
	echo( '<tr>' . "\n" );
	echo( '<td style="border-top: 0px;">&nbsp;</td>' . "\n" );
	echo( '<td style="border-top: 0px;">&nbsp;</td>' . "\n" );
	echo( '<td style="border-top: 0px;">&nbsp;</td>' . "\n" );
	echo( '<td style="border-top: 0px;"><strong>SECOND TRAIT</strong></td>' . "\n" );
	echo( '<td style="border-top: 0px;">' . compare_result( $g_arr_problem_info->trait_B_parent_A_mean, $res_solution->trait_B_parent_A_mean ) . '</td>' . "\n" );
	echo( '<td style="border-top: 0px;">' . compare_result( $g_arr_problem_info->trait_B_parent_B_mean, $res_solution->trait_B_parent_B_mean ) . '</td>' . "\n" );
	echo( '<td style="border-top: 0px;">' . compare_result( $g_arr_problem_info->trait_B_var, $res_solution->trait_B_var ) . '</td>' . "\n" );
	echo( '<td style="border-top: 0px;">' . compare_result( $g_arr_problem_info->trait_B_h2, $res_solution->trait_B_h2 ) . '</td>' . "\n" );
	echo( '<td style="border-top: 0px;">' . compare_result( $g_arr_problem_info->trait_B_number_of_genes, $res_solution->trait_B_number_of_genes ) . '</td>' . "\n" );
	echo( '<td style="border-top: 0px;">&nbsp;</td>' . "\n" );
	echo( '<td style="border-top: 0px;"><input class="buttoninput" type="button" value="View Progress" onclick="openProgress( event, ' . $g_int_problem_id . ', \'' . $res_solution->UserId . '\' );" /></td>' . "\n" );
	echo( '</tr>' . "\n" );
}

?>
                
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
*                       Set the generations information to
*                       $g_int_number_of_generations
*/
function verify_problem_exists()
{
	global $g_obj_problem_manager, $g_obj_db, $g_arr_problem_info, $g_int_problem_id, $g_obj_solution_manager, $g_res_student_solutions;
	
	$res_problem = $g_obj_problem_manager->view_problem_details( $g_int_problem_id );
	
	if ( $g_obj_db->get_number_of_rows( $res_problem ) == 0 )
	{
		MessageHandler::add_message( MSG_ERROR, 'The Problem does not exist' );
	}
	
	else
	{
		$g_arr_problem_info = $g_obj_db->fetch( $res_problem );
	}
}

/**  Function: string compare_result( $dbl_answer, $dbl_answer_from_student )
*    ---------------------------------------------------------------- 
*    Purpose:           Compare the answer with the student answer
*    Arguments:         None
*                       
*    Returns/Assigns:   Returns a string containing the result of 
*                       the comparison
*/
function compare_result( $dbl_answer, $dbl_answer_from_student )
{
	global $g_dbl_range_of_acceptance;
	
	$dbl_range = $dbl_answer * ( $g_dbl_range_of_acceptance / 100 );
	
	$dbl_lower_range = $dbl_answer - $dbl_range;
	$dbl_upper_range = $dbl_answer + $dbl_range;
	
	$dbl_formatted_answer = PageHandler::format_precision( (double)$dbl_answer_from_student, 2 );
	$dbl_formatted_range = PageHandler::format_precision( ( (double)$dbl_answer_from_student - (double)$dbl_answer ) / (double)$dbl_answer, 2 ) * 100;
	
	if ( $dbl_answer_from_student >= $dbl_lower_range && $dbl_answer_from_student <= $dbl_upper_range )
	{
		$str_output = sprintf( '<span class="correctanswer">%s (%s%%)</span>', htmlspecialchars( $dbl_formatted_answer ), htmlspecialchars( $dbl_formatted_range ) );
	}
	
	else
	{   
		$str_output = sprintf( '<span class="wronganswer">%s (%s%%)</span>', htmlspecialchars( $dbl_formatted_answer ), htmlspecialchars( $dbl_formatted_range ) );
	}
	
	return $str_output;
}

?>
