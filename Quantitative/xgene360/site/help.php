<?php 

/*
* include necessary files
*/

require_once( '../includes/global.inc.php' );

/*
* necessary id
*/

$g_str_parent_page = null;

/*
* initialize common stuff
*/

$g_obj_db = null;
$g_obj_lock = null;
$g_str_serial = null;
$g_obj_user = null;

PageHandler::initialize();
PageHandler::check_permission( array( UP_ADMINISTRATOR, UP_PROFESSOR, UP_TA, UP_STUDENT ) );

/*
* set header stuff
*/

$g_int_privilege = $g_obj_user->int_privilege;

$g_str_page_title = "Help";
$g_arr_scripts = null;
$g_arr_nav_links = $g_arr_nav_defined_links[$g_int_privilege];
$g_str_script_block = "xgene360_cu.using( 'sortabletable' )";

require_once( '../includes/header.inc.php' );

if ( $g_bln_display_content )
{
?>

<!-- Start Content -->

<?php

switch( $g_int_privilege )
{
  case UP_PROFESSOR:
    include( 'helpadminprof.inc' );
    break;
  case UP_ADMINISTRATOR:
    include( 'helpadminprof.inc' );
    break;
  case UP_TA:
    include( 'helpta.inc' );
    break;
  case UP_STUDENT:
    include( 'helpstudent.inc' );
    break; 
  default:
    include( 'helpstudent.inc' );
    break;
}

?>

<!-- End Content -->

<?php
}
  
require_once( '../includes/footer.inc.php' );

$g_obj_db->disconnect();

?>
