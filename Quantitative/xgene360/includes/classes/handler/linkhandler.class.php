<?php

$g_arr_nav_defined_links = array();

$g_arr_nav_defined_links[UP_STUDENT] = array( 
  'View Problems' => URLROOT.'student/viewproblems.php'
);

$g_arr_nav_defined_links[UP_TA] = array( 
  'View Courses' => URLROOT.'admin/managecourses.php', 
	'View Problems' => URLROOT.'admin/manageproblems.php',
	'View Students' => URLROOT.'admin/managestudents.php'
);

$g_arr_nav_defined_links[UP_PROFESSOR] = array(
  'Manage Courses' => URLROOT.'admin/managecourses.php', 
	'Manage Problems' => URLROOT.'admin/manageproblems.php', 
	'Manage Professors' => URLROOT.'admin/manageprofessors.php',
	'Manage TAs' => URLROOT.'admin/managetas.php',
	'Manage Students' => URLROOT.'admin/managestudents.php'
);

$g_arr_nav_defined_links[UP_ADMINISTRATOR] = array(
  'Manage Courses' => URLROOT.'admin/managecourses.php', 
	'Manage Problems' => URLROOT.'admin/manageproblems.php', 
	'Manage Professors' => URLROOT.'admin/manageprofessors.php',
	'Manage TAs' => URLROOT.'admin/managetas.php',
	'Manage Students' => URLROOT.'admin/managestudents.php'
);

$g_arr_header_links = array(
	'Help' => URLROOT.'site/help.php'
);

$g_arr_header_login_links = array(
	'Help' => URLROOT.'site/help.php#' . htmlspecialchars( basename( $_SERVER['SCRIPT_FILENAME'], ".php" ) ),
	'Change Password' => URLROOT.'changepassword.php',
	'LOGOUT' => URLROOT.'logout.php'
);

$g_arr_footer_links = array(
  'About XGene 360' => URLROOT.'site/aboutxgene360.php', 
	'About Team 7' => URLROOT.'site/aboutteam7.php' );

$g_arr_calendar_months = array( 
  'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August',
  'September', 'October', 'November', 'December'
);

class LinkHandler
{
	/**  Function: populate_menu_items( $arr_link, $str_page_title ) 
	*    ---------------------------------------------------------------- 
	*    Purpose:           Populates the menu
	*    Arguments:         $arr_link - array, the array containing the 
	*                       link
	*                       $str_page_title - string, the page title
	*                       
	*    Returns/Assigns:   None
	*/
	function populate_menu_items( $arr_link, $str_page_title ) 
	{
		if ( isset( $arr_link ) )
		{
			$tmp_bln_is_first_link = false;

			foreach ( $arr_link as $tmp_str_link_name => $tmp_str_url )
			{
				if ( !$tmp_bln_is_first_link )
				{
					echo( '<li class="first">' );
					$tmp_bln_is_first_link = true;
				}

				else
				{
					echo( '<li>' );
				}

				if( $tmp_str_link_name == $str_page_title )
				{
					echo( $tmp_str_link_name.'</li>' );
				}

				else
				{
					echo( '<a href="'.$tmp_str_url.'">'.$tmp_str_link_name.'</a></li>' );
				}
			}
		}
	}
}

?>
