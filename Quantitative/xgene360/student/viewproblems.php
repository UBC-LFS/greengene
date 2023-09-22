<?php

/*
* include necessary files
*/

require_once( '../includes/global.inc.php' );
require_once( '../includes/classes/db/problemmanager.class.php' );
require_once( '../includes/classes/db/generationmanager.class.php' );

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

$g_str_page_title = "View Problems";
$g_arr_nav_links = $g_arr_nav_defined_links[$g_obj_user->int_privilege];
$g_str_script_block = "xgene360_cu.using( 'sortabletable' )";

require_once( '../includes/header.inc.php' );

if ( $g_bln_display_content )
{
?>

<!-- Start Content -->
<table border="0" cellpadding="0" cellspacing="0" class="listing" id="ListOfProblems">
  <tr>
    <th width="150">Problem</th>
	<th width="100">Course</th>
    <th>Breeding History</th>
  </tr>

<?php

	$res_problems = $g_obj_problem_manager->view_problems();

	if ( $g_obj_db->get_number_of_rows( $res_problems ) == 0 )
	{
		echo( '<tr>' . "\n" );
		echo( '<td colspan="5">There is no problem assigned to you</td>' . "\n" );
		echo( '</tr>' . "\n" );
	}
	
	else
	{
		for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $res_problems ); ++$i )
		{
			$res_row = $g_obj_db->fetch( $res_problems );

			$res_number_of_generations = $g_obj_generation_manager->get_number_of_generations( $g_obj_user->str_username, $res_row->problem_id );
			$res_gen = $g_obj_db->fetch( $res_number_of_generations );
			
			$int_generation_num = $res_gen->generation_count;
		
			echo( '<tr onclick="openProblemDetail( \'' . htmlspecialchars( $res_row->problem_id ) .'\' );" onmouseover="hightlightSelectedRow( this, true );" onmouseout="hightlightSelectedRow( this, false );">' . "\n" );
			echo( '<td>' . htmlspecialchars( $res_row->problem_name ) . '</td>' . "\n" );
			echo( '<td>' . htmlspecialchars( $res_row->Name ) . '</td>' . "\n" );
			echo( '<td>' . htmlspecialchars( $int_generation_num ) . '&nbsp;/&nbsp;' . htmlspecialchars( $res_row->max_cross ) . '</td>' . "\n" );
			echo( '</tr>' . "\n" );
		}
	}
    
?>

</table>
<!-- End Content -->

<?php
}

require_once( '../includes/footer.inc.php' );

$g_obj_db->disconnect();

?>
