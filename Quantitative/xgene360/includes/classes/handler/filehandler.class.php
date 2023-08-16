<?php

// for mac compatibility
ini_set( "auto_detect_line_endings", 1 );

class FileHandler
{
	/**  Function: import_student_list( $obj_file, $int_greengene_course )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Import the student list
	*    Arguments:         $obj_file - object, the file
	*                       $int_greengene_course - int, the greengene
	*                       course id
	*
	*    Returns/Assigns:   None
	*/
	function import_student_list( $obj_file, $int_greengene_course )
	{
		global $g_obj_student_manager;
		
		// read the lines
		$arr_lines = file( $obj_file );
		
		$arr_success = array();
		$arr_fail = array();
		
		for ( $i = 0; $i < count( $arr_lines ); ++$i )
		{
			$line = FileHandler::trim_string( $arr_lines[$i] );
			
			list( $str_student_number, $str_first_name, $str_last_name ) = explode( ',', $line );
		
			if ( isset( $str_student_number ) && isset( $str_first_name ) && isset( $str_last_name ) )
			{
				$str_student_number = FileHandler::trim_string( $str_student_number );
				$str_first_name = FileHandler::trim_string( $str_first_name );
				$str_last_name = FileHandler::trim_string( $str_last_name );
				
				// generate username and password for this user
				$str_user_name = $g_obj_student_manager->autogen_user( $str_first_name, $str_last_name );
				$str_password = $g_obj_student_manager->autogen_password( $str_first_name, $str_last_name );
				
				$arr_tmp = array();
				$arr_tmp[0] = $str_user_name;
				$arr_tmp[1] = $str_password;
				
				// add the user to the database
				if ( $g_obj_student_manager->create_user( $str_user_name, $int_greengene_course, UP_STUDENT,  $str_first_name, $str_last_name, $str_password, $str_student_number ) )
				{
					array_push( $arr_success, $arr_tmp );
				}
				
				else
				{
					array_push( $arr_fail, $arr_tmp );
				}
			}
		}
		
		if ( count( $arr_success ) != 0 )
		{
			$str_message = PageHandler::display_users_id_password( 'Successfully created', $arr_success );
		
			MessageHandler::add_message( MSG_SUCCESS, $str_message );
		}
		
		if ( count( $arr_fail ) != 0 )
		{
			$str_message = PageHandler::display_users_id_password( 'Failed to create', $arr_fail );
		
			MessageHandler::add_message( MSG_FAIL, $str_message );
		}
	}
	
	/**  Function: export_student_list( $arr_student_ids )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Export the student list
	*    Arguments:         $arr_student_ids - array, the student ids
	*
	*    Returns/Assigns:   None
	*/
	function export_student_list( $arr_student_ids )
	{		
		global $g_obj_student_manager, $g_obj_db;
		
		$res_students = $g_obj_student_manager->view_students();

		$str_output = '';
		
		for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $res_students ); ++$i )
		{
			$res_row = $g_obj_db->fetch( $res_students );
			
			for ( $j = 0; $j < count( $arr_student_ids ); ++$j )
			{
				if ( $arr_student_ids[$j] == $res_row->UserId )
				{
					$str_output = $str_output . '"' . $res_row->StudentNum . '","' . $res_row->FirstName . '","' . $res_row->LastName . '","' . $res_row->UserId . '"' . "\r\n";
					array_splice( $arr_student_ids, $j, 1 );
					
					break;
				}
			}
			
			if ( count( $arr_student_ids ) == 0 )
			{
				break;
			}
		}
		
		FileHandler::send_downloadable_text( 'student.csv', $str_output );
	}
	
	/**  Function: import_ta_list( $obj_file, $int_greengene_course )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Import the TA list
	*    Arguments:         $obj_file - object, the file
	*                       $int_greengene_course - int, the greengene
	*                       course id
	*
	*    Returns/Assigns:   None
	*/
	function import_ta_list( $obj_file, $int_greengene_course )
	{
		global $g_obj_ta_manager;
		
		// read the lines
		$arr_lines = file( $obj_file );
		
		$arr_success = array();
		$arr_fail = array();
		
		for ( $i = 0; $i < count( $arr_lines ); ++$i )
		{
			$line = FileHandler::trim_string( $arr_lines[$i] );
			
			list( $str_first_name, $str_last_name ) = explode( ',', $line );
		
			if ( isset( $str_first_name ) && isset( $str_last_name ) )
			{
				$str_first_name = FileHandler::trim_string( $str_first_name );
				$str_last_name = FileHandler::trim_string( $str_last_name );
				
				// generate username and password for this user
				$str_user_name = $g_obj_ta_manager->autogen_user( $str_first_name, $str_last_name );
				$str_password = $g_obj_ta_manager->autogen_password( $str_first_name, $str_last_name );
				
				$arr_tmp = array();
				$arr_tmp[0] = $str_user_name;
				$arr_tmp[1] = $str_password;
				
				// add the user to the database
				if ( $g_obj_ta_manager->create_user( $str_user_name, $int_greengene_course, UP_TA,  $str_first_name, $str_last_name, $str_password, 0 ) )
				{
					array_push( $arr_success, $arr_tmp );
				}
				
				else
				{
					array_push( $arr_fail, $arr_tmp );
				}
			}
		}
		
		if ( count( $arr_success ) != 0 )
		{
			$str_message = PageHandler::display_users_id_password( 'Successfully created', $arr_success );
		
			MessageHandler::add_message( MSG_SUCCESS, $str_message );
		}
		
		if ( count( $arr_fail ) != 0 )
		{
			$str_message = PageHandler::display_users_id_password( 'Failed to create', $arr_fail );
		
			MessageHandler::add_message( MSG_FAIL, $str_message );
		}
	}
	
	/**  Function: export_ta_list( $arr_ta_ids )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Export the TA list
	*    Arguments:         $arr_ta_ids - array, the TA ids
	*
	*    Returns/Assigns:   None
	*/
	function export_ta_list( $arr_ta_ids )
	{
		global $g_obj_ta_manager, $g_obj_db;
		
		$res_tas = $g_obj_ta_manager->view_tas();

		$str_output = '';
		
		for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $res_tas ); ++$i )
		{
			$res_row = $g_obj_db->fetch( $res_tas );
			
			for ( $j = 0; $j < count( $arr_ta_ids ); ++$j )
			{
				if ( $arr_ta_ids[$j] == $res_row->UserId )
				{
					$str_output = $str_output . '"' . $res_row->FirstName  . '","' . $res_row->LastName . '","' . $res_row->UserId . '"' . "\r\n";
					array_splice( $arr_ta_ids, $j, 1 );
					
					break;
				}
			}
			
			if ( count( $arr_ta_ids ) == 0 )
			{
				break;
			}
		}
		
		FileHandler::send_downloadable_text( 'ta.csv', $str_output );
	}
	
	/**  Function: import_professor_list( $obj_file, $int_greengene_course )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Import the professor list
	*    Arguments:         $obj_file - object, the file
	*                       $int_greengene_course - int, the greengene
	*                       course id
	*
	*    Returns/Assigns:   None
	*/
	function import_professor_list( $obj_file, $int_greengene_course )
	{
		global $g_obj_professor_manager;
				
		// read the lines
		$arr_lines = file( $obj_file );
		
		$arr_success = array();
		$arr_fail = array();
		
		for ( $i = 0; $i < count( $arr_lines ); ++$i )
		{
			$line = FileHandler::trim_string( $arr_lines[$i] );
			
			list( $str_first_name, $str_last_name ) = explode( ',', $line );
		
			if ( isset( $str_first_name ) && isset( $str_last_name ) )
			{
				$str_first_name = FileHandler::trim_string( $str_first_name );
				$str_last_name = FileHandler::trim_string( $str_last_name );
				
				// generate username and password for this user
				$str_user_name = $g_obj_professor_manager->autogen_user( $str_first_name, $str_last_name );
				$str_password = $g_obj_professor_manager->autogen_password( $str_first_name, $str_last_name );
				
				$arr_tmp = array();
				$arr_tmp[0] = $str_user_name;
				$arr_tmp[1] = $str_password;
				
				// add the user to the database
				if ( $g_obj_professor_manager->create_user( $str_user_name, $int_greengene_course, UP_PROFESSOR,  $str_first_name, $str_last_name, $str_password, 0 ) )
				{
					array_push( $arr_success, $arr_tmp );
				}
				
				else
				{
					array_push( $arr_fail, $arr_tmp );
				}
			}
		}
		
		if ( count( $arr_success ) != 0 )
		{
			$str_message = PageHandler::display_users_id_password( 'Successfully created', $arr_success );
		
			MessageHandler::add_message( MSG_SUCCESS, $str_message );
		}
		
		if ( count( $arr_fail ) != 0 )
		{
			$str_message = PageHandler::display_users_id_password( 'Failed to create', $arr_fail );
		
			MessageHandler::add_message( MSG_FAIL, $str_message );
		}
	}
	
	/**  Function: export_professor_list( $arr_professor_ids )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Export the professor list
	*    Arguments:         $arr_professor_ids - array, the professor ids
	*
	*    Returns/Assigns:   None
	*/
	function export_professor_list( $arr_professor_ids )
	{
		global $g_obj_professor_manager, $g_obj_db;
		
		$res_professors = $g_obj_professor_manager->view_professors();

		$str_output = '';
		
		for ( $i = 0; $i < $g_obj_db->get_number_of_rows( $res_professors ); ++$i )
		{
			$res_row = $g_obj_db->fetch( $res_professors );
			
			for ( $j = 0; $j < count( $arr_professor_ids ); ++$j )
			{
				if ( $arr_professor_ids[$j] == $res_row->UserId )
				{
					$str_output = $str_output . '"' . $res_row->FirstName . '","' . $res_row->LastName . '","' . $res_row->UserId . '"' . "\r\n";
					array_splice( $arr_professor_ids, $j, 1 );
					
					break;
				}
			}
			
			if ( count( $arr_professor_ids ) == 0 )
			{
				break;
			}
		}
		
		FileHandler::send_downloadable_text( 'professor.csv', $str_output );
	}
	
	/**  Function: send_downloadable_text( $str_filename, $str_output )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Send the data as downloadable text
	*    Arguments:         $str_filename - string, the file name
	*                       $str_output - string, the output string
	*
	*    Returns/Assigns:   None
	*/
	function send_downloadable_text( $str_filename, $str_output )
	{
		header( "Content-Type: application/octet-stream\n" );
		header( "Content-Disposition: attachment; filename=\"" . $str_filename . "\"\n" );
		header( "Content-length: " . strlen( $str_output ) . "\n" );
		header( "Cache-Control: public\n" );
		
		echo( $str_output );
		
		exit();
	}
	
	/**  Function: trim_string( $str_value )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Trim the string for import
	*    Arguments:         $str_value - string, the value
	*
	*    Returns/Assigns:   Returns the string after trimmed
	*/
	function trim_string( $str_value )
	{
		return trim( trim( $str_value ), "\"" );
	}
}

?>
