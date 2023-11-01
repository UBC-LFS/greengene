<?php

/*
* include necessary files
*/

require_once( '../includes/global.inc.php' );

/*
* initialize common stuff
*/

$g_obj_db = null;
$g_obj_lock = null;
$g_str_serial = null;
$g_obj_user = null;

// PageHandler::initialize();
// PageHandler::check_permission( array( UP_STUDENT ) ); // WAS COMMENTED OUT BEFORE PHP8 MIGRATION

$pageHandler = (new PageHandler);
(new PageHandler) -> initialize();

/*
* required info
*/

$g_str_page_title = "About XGene 360";

if ( $g_obj_user == null )
{ $g_arr_nav_links = array( 
  'Login' => URLROOT.'login.php'
  ); 
}
else {$g_arr_nav_links = $g_arr_nav_defined_links[$g_obj_user->int_privilege];}

// $g_str_script_block = "xgene360_cu.using( 'sortabletable' )";

require_once( '../includes/header.inc.php' );


?>

<!-- Start Content -->

<table class="format">

  <tr>
    <td style="vertical-align: top; text-align: center;">
      <img src="/includes/images/main_logo.gif" />
    </td>
    <td width="50">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
    <td>
        <strong>XGene 360</strong> (an expansion of GreenGene) is a web-based 
        application that will allow a professor to provide students with an 
        interactive learning experience in quantitative plant breeding genetics. This 
        application simulates plant breeding between diploid plants of the same species 
        under similar environment conditions, employing basic concepts and principles of 
        plant breeding and quantitative genetics.  
        <br /><br />
        Students will be able to select offsprings for inter-crossing 
        in creating the next generation of plants. For the professor, they will be able 
        to generate a unique problem for each student user by specifying paramters for
        two independent (non-linked) traits in the parent generation. The professor will 
        also be able to assign problems to courses of students and view the progress of 
        any student (i.e. see the status of an individual student's problem without the 
        capability to make any changes to it). Teaching assistants (TAs) will be able
        to aid a professor by being able to view the progress of any student from a course 
        assigned to them. Through the manipulation and analysis of progeny, students 
        will be able to determine the problem parameters defined by the professor, thus 
        enriching their experience with quantitative plant breeding.
    </td>
  </tr>

</table>

<!-- End Content -->

<?php

require( '../includes/footer.inc.php' );

?>

