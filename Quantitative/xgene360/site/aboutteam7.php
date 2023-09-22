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
$pageHandler -> initialize();

/*
* required info
*/

$g_str_page_title = "About Team 7";

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
    <td width="150">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
    <td>
        <h3>Members of Team 7:</h3>
        <ul>
          <li>Charles Bihis</li>
          <li>Jason Hui</li>
          <li>Gabriel Lee</li>
          <li>Jack Liu</li>
          <li>Susannah Poon</li>
          <li>Jimmy Wong</li>
        </ul>
    </td>
  </tr>

</table>

<!-- End Content -->

<?php

require( '../includes/footer.inc.php' );

?>

