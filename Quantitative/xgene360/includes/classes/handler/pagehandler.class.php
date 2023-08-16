<?php

class PageHandler
{
	/**  Function: void redirect_initial_page( $int_user_privilege )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Redirect the user to the initial page
	*    Arguments:         $int_user_privilege - int, the user privilege
	*                       
	*    Returns/Assigns:   None
	*/
	function redirect_initial_page( $int_user_privilege )
	{
		switch ( $int_user_privilege )
		{
			case UP_ADMINISTRATOR:
			case UP_PROFESSOR:
				PageHandler::redirect( URLROOT.'admin/managecourses.php' );
			break;
		    
			case UP_TA:
				PageHandler::redirect( URLROOT.'admin/manageproblems.php' );
			break;  
		    
			case UP_STUDENT:
				PageHandler::redirect( URLROOT.'student/viewproblems.php' );
			break;
		    
			default:
				PageHandler::redirect( URLROOT.'login.php' );
			break;  
		}
	}
  
	/**  Function: void redirect( $str_url )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Redirect the user to another page
	*    Arguments:         $str_url - the url
	*                       
	*    Returns/Assigns:   None
	*/
	function redirect( $str_url )
	{
		header( "Location: $str_url" );
		exit;
	}
  

	/**  Function: object get_post_value( $str_field_name )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Returns the post value if available
	*    Arguments:         $str_field_name - string, the field name
	*                       
	*    Returns/Assigns:   If the field exists, returns the field value;
	*                       otherwise, returns false
	*/
	function get_post_value( $str_field_name )
	{
		if ( isset( $_POST[$str_field_name] ) )
		{
			if ( is_string( $_POST[$str_field_name] ) && strlen( $_POST[$str_field_name] ) == 0 )
			{
				return null;
			}
			
			if ( is_array( $_POST[$str_field_name] ) && count( $_POST[$str_field_name] ) == 0 )
			{
				return null;
			}
			
			return $_POST[$str_field_name];
		}
		
		return null;
	}
	
	/**  Function: string write_post_value_if_failed( $str_field_name )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Returns the post value if the there is
	*                       MSG_ERROR type messages
	*    Arguments:         $str_field_name - string, the field name
	*                       
	*    Returns/Assigns:   The field value
	*/
	function write_post_value_if_failed( $str_field_name )
	{
		global $g_bln_fail;
		
		if ( $g_bln_fail )
		{
			return PageHandler::get_post_Value( $str_field_name );
		}
		
		return null;
	}
	
	/**  Function: void initialize()
	*    ---------------------------------------------------------------- 
	*    Purpose:           Initialize the global variables
	*    Arguments:         None
	*                       
	*    Returns/Assigns:   $g_obj_db for database,
	*                       $g_obj_lock for lock mechanism,
	*                       $g_str_serial for unique serial,
	*                       $g_obj_user for the user
	*/
	function initialize()
	{
		global $g_obj_db, $g_obj_lock, $g_str_serial, $g_obj_user;
		
		$g_obj_db = new DBManager();
		$g_obj_lock = new LockManager( $g_obj_db );
		
		$g_str_serial = $g_obj_lock->get_serial();

		$g_obj_user = CookieHandler::get_user();
	}
	
	/**  Function: void check_permission()
	*    ---------------------------------------------------------------- 
	*    Purpose:           Check if the user has permission to 
	*                       access this page
	*    Arguments:         None
	*                       
	*    Returns/Assigns:   $arr_allowed_users - array, list of user
	*                       privilege that has access to the page
	*/
	function check_permission( $arr_allowed_users )
	{
		global $g_obj_user;
		
		if ( $g_obj_user == null )
		{
			PageHandler::redirect_initial_page( UP_INVALID );
		}
		
		foreach ( $arr_allowed_users as $int_allowed_user )
		{
			if ( $g_obj_user->int_privilege == $int_allowed_user )
			{
				return;
			}
		}
		
		// the user is not in the permission list
		PageHandler::redirect_initial_page( UP_INVALID );
	}
	
	/**  Function: void check_necessary_id( $arr_required_ids, $str_url )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Redirects the page if necessary id is not 
	*                       passed to the page
	*    Arguments:         $arr_required_ids, 
	*                       
	*    Returns/Assigns:   None
	*/
	function check_necessary_id( $arr_required_ids, $str_url )
	{
		foreach ( $arr_required_ids as $str_required_id )
		{
			if ( !isset( $_GET[ $str_required_id ] ) )
			{
				PageHandler::redirect( $str_url );
			}
		}
	}
	
	/**  Function: string format_date( $int_time_stamp )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Format the timestamp to a common format
	*    Arguments:         $int_time_stamp - int, the timestamp
	*                       
	*    Returns/Assigns:   The formatted date
	*/
	function format_date( $int_time_stamp )
	{
		return date( 'Y/m/d H:i:s', $int_time_stamp );
	}
	
	/**  Function: string generate_year( $str_name, $int_start_year, $int_selected_year )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Generate HTML code for picking year
	*    Arguments:         $str_name - string, the field name
	*                       $int_start_year - int, the starting year
	*                       $int_selected_year - int, the selected year
	*                       
	*    Returns/Assigns:   The generated html code
	*/
	function generate_year( $str_name, $int_start_year, $int_selected_year )
	{
		$str_output = '<select name="' . $str_name . '" id="' . $str_name . '">';
		
		for ( $i = 1; $i < 11; ++$i )
		{
			$str_output = $str_output. '<option ';
			
			if ( $i == $int_selected_year )
			{
				$str_output = $str_output . 'selected="selected" ';
			}
			
			$str_output = $str_output . 'value="' . $int_start_year . '">' . $int_start_year . '</option>';
			
			$int_start_year = $int_start_year + 1;
		}
		
		$str_output = $str_output . '</select>';
		
		return $str_output;
	}
	
	/**  Function: string generate_month( $str_name, $int_selected_month )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Generate HTML code for picking month
	*    Arguments:         $str_name - string, the field name
	*                       $int_selected_month - int, the selected month
	*                       
	*    Returns/Assigns:   The generated html code
	*/
	function generate_month( $str_name, $int_selected_month )
	{
		global $g_arr_calendar_months;
		
		$str_output = '<select name="' . $str_name . '" id="' . $str_name . '">';
		
		for ( $i = 1; $i <= 12; ++$i )
		{
			$str_output = $str_output . '<option ';
			
			if ( $i == $int_selected_month )
			{
				$str_output = $str_output . 'selected="selected" ';
			}
			
			$str_month = $i;
			
			if ( $i < 10 )
			{
				$str_month = '0' . $str_month;
			}
			
			$str_output = $str_output . 'value="' . $str_month . '">' . $g_arr_calendar_months[ $i - 1 ] . '</option>';
		}
		
		$str_output = $str_output . '</select>';
		
		return $str_output;
	}
	
	/**  Function: string generate_day( $str_name, $int_selected_day )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Generate HTML code for picking day
	*    Arguments:         $str_name - string, the field name
	*                       $int_selected_day - int, the selected day
	*                       
	*    Returns/Assigns:   The generated html code
	*/
	function generate_day( $str_name, $int_selected_day )
	{
		$str_output = '<select name="' . $str_name . '" id="' . $str_name . '">';
		
		for ( $i = 1; $i <= 31; $i++ )
		{
			$str_output = $str_output . '<option ';
			
			if ( $i == $int_selected_day )
			{
				$str_output = $str_output . 'selected="selected" ';
			}
			
			$str_day = $i;
			
			if ( $i < 10 )
			{
				$str_day = '0' . $i;
			}
			
			$str_output = $str_output . 'value="' . $i . '">' . $i . '</option>';
		}
		
		$str_output = $str_output . '</select>';
		
		return $str_output;
	}
	
	/**  Function: string generate_hour( $str_name, $str_selected_hour )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Generate HTML code for picking hour
	*    Arguments:         $str_name - string, the field name
	*                       $str_selected_hour - string, the selected hour
	*                       
	*    Returns/Assigns:   The generated html code
	*/
	function generate_hour( $str_name, $str_selected_hour )
	{
		$str_output = '<select name="' . $str_name . '" id="' . $str_name . '">';
		
		for ( $i = 0; $i < 24; $i++ )
		{
			$str_output = $str_output . '<option ';
			
			$str_hour = $i;
			
			if ( $i < 10 )
			{
				$str_hour = '0' . $str_hour;
			}
			
			if ( $str_hour == $str_selected_hour )
			{
				$str_output = $str_output . 'selected="selected" ';
			}
			
			$str_output = $str_output . 'value="' . $str_hour . '">' . $str_hour . '</option>';
		}
		
		$str_output = $str_output . '</select>';
		
		return $str_output;
	}
	
	/**  Function: string generate_minute( $str_name, $str_selected_minute )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Generate HTML code for picking minute
	*    Arguments:         $str_name - string, the field name
	*                       $str_selected_minute - string, the selected minute
	*                       
	*    Returns/Assigns:   The generated html code
	*/
	function generate_minute( $str_name, $str_selected_minute )
	{
		$str_output = '<select name="' . $str_name . '" id="' . $str_name . '">';
		
		for ( $i = 0; $i < 60; $i++ )
		{
			$str_output = $str_output . '<option ';
			
			$str_minute = $i;
			
			if ( $i < 10 )
			{
				$str_minute = '0' . $str_minute;
			}
			
			if ( $str_minute == $str_selected_minute )
			{
				$str_output = $str_output . 'selected="selected" ';
			}
			
			$str_output = $str_output . 'value="' . $str_minute . '">' . $str_minute . '</option>';
		}
		
		$str_output = $str_output . '</select>';
		
		return $str_output;
	}
	
	/**  Function: string format_precision( $dbl_value, $int_precision )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Format float value to certain precision
	*    Arguments:         $dbl_value - double, the value
	*                       $int_precision - int, the precision
	*                       
	*    Returns/Assigns:   The formatted number
	*/
	function format_precision( $dbl_value, $int_precision )
	{
		$dbl_value = $dbl_value * pow( 10, $int_precision + 1 );
		$dbl_value = floor( $dbl_value );
		$dbl_value = (float)$dbl_value / 10;
		(float) $dbl_mod = $dbl_value - floor( $dbl_value );
		$dbl_value = floor( $dbl_value );
		
		if ( $dbl_mod > 0.5 ) 
		{
			$dbl_value++;
		}
		
		return $dbl_value / pow( 10, $int_precision );
	}
	
	/**  Function: string display_users_id_name( $str_message, $arr_users )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Format the message to display id and
	*                       name pair
	*    Arguments:         $str_message - string, the prepend message
	*                       $arr_users - array, the list of users
	*
	*    Returns/Assigns:   The string with list of users
	*/
	function display_users_id_name( $str_message, $arr_users )
	{
		$str_message = $str_message . "<ul>";
		
		for ( $i = 0; $i < count( $arr_users ); ++$i )
		{
			$str_message = $str_message . "<li>'" . $arr_users[$i][1] . " " . $arr_users[$i][2] . "'</li>";
		}
		
		$str_message = $str_message . "</ul>";
		
		return $str_message;
	}
	
	/**  Function: string display_users_id_password( $str_message, $arr_users )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Format the message to display id and
	*                       password pair
	*    Arguments:         $str_message - string, the prepend message
	*                       $arr_users - array, the list of users
	*
	*    Returns/Assigns:   The string with list of users
	*/
	function display_users_id_password( $str_message, $arr_users )
	{
		$str_message = $str_message . "<ul>";
		
		for ( $i = 0; $i < count( $arr_users ); ++$i )
		{
			$str_message = $str_message . "<li>'" . $arr_users[$i][0] . "' with '" . $arr_users[$i][1] . "'</li>";
		}
		
		$str_message = $str_message . "</ul>";
		
		return $str_message;
	}

	/**  Function: string display_users_cwl( $str_message, $arr_cwl )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Format the message to display cwl username
	*    Arguments:         $str_message - string, the prepend message
	*                       $arr_cwl - array, the list of cwl
	*
	*    Returns/Assigns:   The string with list of cwl
	*/
	function display_users_cwl( $str_message, $arr_cwl )
	{
		$str_message = $str_message . "<ul>";
		
		for ( $i = 0; $i < count( $arr_cwl ); ++$i )
		{
			$str_message = $str_message . "<li>'" . $arr_cwl[$i] . "'</li>";
		}
		
		$str_message = $str_message . "</ul>";
		
		return $str_message;
	}
}

?>
