<?php

class UserManager
{
	var $m_obj_user = null;
	var $m_obj_db = null;

/**
 * Class Constructor
 * PRE: $obj_user is a user object from the cookie; 
 *      $obj_db is a DBManager object
 * POST: a UserManager object has been created with the parameters
 * @param $obj_user, $obj_db
 */
	function UserManager( $obj_user, $obj_db )
	{
		$this->m_obj_user = $obj_user;
		$this->m_obj_db = $obj_db;
	}

/**  Function: arr_user_ids_names user_name_list
*    ---------------------------------------------------------------- 
*    Purpose:           translates the user id to user names for the front end 
*    Arguments:         an array of user ids
*                       
*    Returns/Assigns:   an array of arrays of (user id, first name, last name)
*/
    function user_names_list( $arr_user_id )
    {
		$arr_user_ids_names = array();
		
		for( $i = 0; $i < count( $arr_user_id ); $i++ )
		{
			$res_user = $this->view_user( $arr_user_id[$i]);
			$res_row = $this->m_obj_db->fetch( $res_user );

			if ( $res_row != null && $res_row != false )
			{
				$arr_id_firstlastname = array();
				array_push( $arr_id_firstlastname, $res_row->UserId );
				array_push( $arr_id_firstlastname, $res_row->FirstName );
				array_push( $arr_id_firstlastname, $res_row->LastName );

				array_push( $arr_user_ids_names, $arr_id_firstlastname );
			}
		}
		
		return $arr_user_ids_names; 
	}

/**  Function: resource view_user
*    ---------------------------------------------------------------- 
*    Purpose:           views a particular user detail
*    Arguments:         user id of the person being viewed
*                       
*    Returns/Assigns:   resource or false or null
*/
	function view_user( $str_user_id )
	{
		$str_sql_query = null;
		$str_this_user = $this->m_obj_user->str_username;

		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_TA:
			{
				$str_sql_query = "SELECT U.UserId, U.FirstName, U.LastName, U.StudentNum "
							   . "From User U "
							   . "WHERE U.PrivilegeLvl = " . UP_STUDENT . " "
							   . "AND U.UserId = '" . $this->m_obj_db->format_sql_string( $str_user_id ) . "'";
				break;
			}

			case UP_PROFESSOR:
/*			{
				if ( ( strcmp( $str_user_id, $str_this_user ) ) == 0 )
				{
					$str_sql_query = "SELECT U.UserId, U.FirstName, U.LastName, U.StudentNum "
								   . "FROM User U "
								   . "WHERE U.PrivilegeLvl = " . UP_PROFESSOR . " "
								   . "AND U.UserId = '" . $this->m_obj_db->format_sql_string( $str_user_id ) . "'";
				}
				else 
				{
					$str_sql_query = "SELECT U.UserId, U.FirstName, U.LastName, U.StudentNum "
								   . "FROM User U "
								   . "WHERE U.PrivilegeLvl <> " . UP_ADMINISTRATOR . " "
								   . "AND U.PrivilegeLvl <> " . UP_PROFESSOR . " "
								   . "AND U.UserId = '" . $this->m_obj_db->format_sql_string( $str_user_id ) . "'";
				}
				break;
			}
*/
			case UP_ADMINISTRATOR:
			{
				$str_sql_query = "SELECT U.UserId, U.FirstName, U.LastName, U.StudentNum "
							   . "From User U "
							   . "WHERE U.UserId = '" . $this->m_obj_db->format_sql_string( $str_user_id ) . "'";
				break;
			}
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to view the user details of " . $this->m_obj_db->format_sql_string( $str_user_id ) );
			return null;
		}

		(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " viewed the user details of " . $this->m_obj_db->format_sql_string( $str_user_id ) );
  		return $this->m_obj_db->query_select( $str_sql_query );
	}
	
/**  Function: boolean create_user
*    ---------------------------------------------------------------- 
*    Purpose:           create a new user
*    Arguments:         user id, course id, user privilege calss, first name, last name
*                       
*    Returns/Assigns:   boolean or null
*/
	function create_user( $str_user_id, $int_user_privilege, $str_first_name, $str_last_name )
	{
		// TODO: remove int_course_id - int_course_id is used in greengene Qualitative no longer relevant
		$int_course_id = 0;
		$str_sql_query = null;
		$res_check_if_exists = null;
		$str_this_user = $this->m_obj_user->str_username;
		$bln_success = true;

		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_PROFESSOR:
			case UP_ADMINISTRATOR:
			{
				$str_sql_query = "BEGIN";

				if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
				{
					$bln_success = false;
					break;
				}

				$res_check_if_exists = $this->view_user( $str_user_id );

				if ( $res_check_if_exists == null )
				{
					$str_sql_query = null;
					break;
				}

				if ( $res_check_if_exists == false )
				{
					$bln_success = false;
					break;
				}

				if ( $this->m_obj_db->get_number_of_rows( $res_check_if_exists ) == 0 ) 
				{
					$str_sql_query = "INSERT INTO User(UserId, CourseId, PrivilegeLvl, FirstName, LastName) "
								   . "VALUES ('" . $this->m_obj_db->format_sql_string( $str_user_id ) . "', "
								   . $this->m_obj_db->format_sql_string( $int_course_id ) . ", "
								   . $this->m_obj_db->format_sql_string( $int_user_privilege ) . ", '"
								   . $this->m_obj_db->format_sql_string( $str_first_name ) . "', '"
								   . $this->m_obj_db->format_sql_string( $str_last_name ) . "')";

					if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
					{
						$bln_success = false;
						break;
					}

					$str_sql_query = "COMMIT";

					if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
					{
						$bln_success = false;
						break;
					}
				}
				else
				{
					$str_sql_query = "ROLLBACK";

					if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
					{
						$bln_success = false;
						break;
					}

					(new Log) -> write_log_with_ip( LOG_TRANSACTION, $this->m_obj_db->format_sql_string( $str_user_id ) . " "
														   . " already exists");
					return false;														   
				}

				break;
			}
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to create user " 
												   . $this->m_obj_db->format_sql_string( $str_user_id ) . ", "
												   . $this->m_obj_db->format_sql_string( $str_first_name ) . " "
												   . $this->m_obj_db->format_sql_string( $str_last_name ) );
			return false;
		}

		if ( !$bln_success )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to create user " 
												   . $this->m_obj_db->format_sql_string( $str_user_id ) . ", "
												   . $this->m_obj_db->format_sql_string( $str_first_name ) . " "
												   . $this->m_obj_db->format_sql_string( $str_last_name ) 
												   . " due to database error" );
			return false;
		}

		(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " created user " 
											   . $this->m_obj_db->format_sql_string( $str_user_id ) . ", "
											   . $this->m_obj_db->format_sql_string( $str_first_name ) . " "
											   . $this->m_obj_db->format_sql_string( $str_last_name ) );
		return true;
	}

/**  Function: boolean modify_user
*    ---------------------------------------------------------------- 
*    Purpose:           modifies the details of a user
*    Arguments:         user id, first name, last name, student number
*                       
*    Returns/Assigns:   boolean or null
*/
	function modify_user( $str_user_id, $str_first_name, $str_last_name)
	{
		$str_sql_query = null;
		$res_check_if_exists = null;
		$str_this_user = $this->m_obj_user->str_username;
		$bln_success = true;

		switch ( $this->m_obj_user->int_privilege )
		{				   
			case UP_TA:
			case UP_PROFESSOR:
			case UP_ADMINISTRATOR:
  			{
				$str_sql_query = "BEGIN";

				if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
				{
					$bln_success = false;
					break;
				}

				$res_check_if_exists = $this->view_user( $str_user_id );

				if ( $res_check_if_exists == null )
				{
					$str_sql_query = null;
					break;
				}

				if ( $res_check_if_exists == false )
				{
					$bln_success = false;
					break;
				}

				if ( $this->m_obj_db->get_number_of_rows( $res_check_if_exists ) > 0 )
				{
					$str_sql_query = "UPDATE User "
								   . "SET FirstName = '". $this->m_obj_db->format_sql_string( $str_first_name ) ."', "
								   . "LastName = '". $this->m_obj_db->format_sql_string( $str_last_name ) . "' "
								   . "WHERE UserId = '" . $this->m_obj_db->format_sql_string( $str_user_id ) . "'";

					if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
					{
						$bln_success = false;
						break;
					}

					$str_sql_query = "COMMIT";

					if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
					{
						$bln_success = false;
						break;
					}
				}
				else
				{
					$str_sql_query = "ROLLBACK";

					if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
					{
						$bln_success = false;
						break;
					}

					(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " does not have permission to modify " 
														   . $this->m_obj_db->format_sql_string( $str_user_id ) . " with "
														   . $this->m_obj_db->format_sql_string( $str_first_name ) . " "
														   . $this->m_obj_db->format_sql_string( $str_last_name ) . " "
														   . " or user "
														   . $this->m_obj_db->format_sql_string( $str_user_id ) 
														   . " does not exist" ); 

					return false;	
				}
				break;
			}
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to modify user " 
												   . $this->m_obj_db->format_sql_string( $str_user_id ) . " with "
												   . $this->m_obj_db->format_sql_string( $str_first_name ) . " "
												   . $this->m_obj_db->format_sql_string( $str_last_name ) );
			return false;
		}
  
		if ( !$bln_success )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to modify user " 
												   . $this->m_obj_db->format_sql_string( $str_user_id ) . " with "
												   . $this->m_obj_db->format_sql_string( $str_first_name ) . " "
												   . $this->m_obj_db->format_sql_string( $str_last_name )
												   . " due to database error" );
			return false;
		}

		(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " modified user " 
											   . $this->m_obj_db->format_sql_string( $str_user_id ) . " with "
											   . $this->m_obj_db->format_sql_string( $str_first_name ) . " "
											   . $this->m_obj_db->format_sql_string( $str_last_name ));
		return true;
	} 

/**  Function: boolean delete user
*    ---------------------------------------------------------------- 
*    Purpose:           deletes a user
*    Arguments:         user id
*                       
*    Returns/Assigns:   boolean or null
*/
	function delete_user ( $str_user_id )
	{
		$str_sql_query = null;
		$res_check_if_exists = null;
		$str_this_user = $this->m_obj_user->str_username;
		$bln_success = true;
		
		switch ( $this->m_obj_user->int_privilege )
		{				   
			case UP_PROFESSOR:
			case UP_ADMINISTRATOR:
  			{
				$str_sql_query = "BEGIN";

				if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
				{
					$bln_success = false;
					break;
				}

				$res_check_if_exists = $this->view_user( $str_user_id );

				if ( $res_check_if_exists == null )
				{
					$str_sql_query = null;
					break;
				}

				if ( $res_check_if_exists == false )
				{
					$bln_success = false;
					break;
				}

				if ( $this->m_obj_db->get_number_of_rows( $res_check_if_exists ) > 0 )
				{
					$str_sql_query = "DELETE FROM User "
								   . "WHERE UserId = '" . $this->m_obj_db->format_sql_string( $str_user_id ) . "'";

					if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
					{
						$bln_success = false;
						break;
					}

					$str_sql_query = "COMMIT";

					if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
					{
						$bln_success = false;
						break;
					}
				}
				else
				{
					$str_sql_query = "ROLLBACK";

					if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
					{
						$bln_success = false;
						break;
					}

					(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " does not have permission to delete user " 
														   . $this->m_obj_db->format_sql_string( $str_user_id ) 
														   . " or user "
														   . $this->m_obj_db->format_sql_string( $str_user_id ) 
														   . " does not exist" );
					return false;
				}
				break;
			}
		}
	
		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}
	
		if ( $str_sql_query == null )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to delete user " 
												   . $this->m_obj_db->format_sql_string( $str_user_id ) );
			return false;
		}

		if ( !$bln_success )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to delete user " 
												   . $this->m_obj_db->format_sql_string( $str_user_id ) 
												   . " due to database error" );
			return false;
		}
		
		(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " deleted user " 
											   . $this->m_obj_db->format_sql_string( $str_user_id ) );
		return true;
	}

	//function view()





// TODO: add log messages to the functions below










	
	function check_with_db ( $str_user_id )
	{
		$str_sql_query = "SELECT U.UserId, U.FirstName, U.LastName, U.StudentNum "		
                       . "From User U "
					   . "WHERE U.UserId = '" . $this->m_obj_db->format_sql_string( $str_user_id ) . "'";
		  
		$int_number_of_rows = $this->m_obj_db->get_number_of_rows( $this->m_obj_db->query_select( $str_sql_query ) );
		
		return $int_number_of_rows > 0;
	}
	
	// TODO: remove - function  generates username for user but changed to CWL username
	function autogen_user ( $str_firstName, $str_lastName )
	{
		$str_lastName = rtrim( $str_lastName );
		$str_username = strtolower( $str_firstName[0] . $str_lastName );
		$int_suffix = 0;
		$int_username_length = strlen( $str_username );
		$int_max_length = 10;
	
		if ( $int_username_length > $int_max_length )
		{
			$str_username = substr( $str_username, 0, $int_max_length );
			$int_username_length = strlen( $str_username );
		}

		// check with database
		while ( $this->check_with_db( $str_username ) ) //$this->check_with_db( $str_username )
		{
			if ( $int_suffix != 0 )
			{
				$str_username = substr( $str_username, 0, $int_username_length - strlen( $int_suffix ) );	
			}
			else if ( $int_username_length == $int_max_length )
			{
				$str_username = substr( $str_username, 0, 9 );	
				$int_username_length = strlen( $str_username );
			}
			
			$int_suffix++;
			$str_username = $str_username . $int_suffix;
			$int_username_length = strlen( $str_username );
			
			if ( $int_username_length > $int_max_length )
			{
				$str_username = substr( $str_username, 0, $int_username_length - ( strlen( $int_suffix ) + 1 ) );
				$str_username = $str_username . $int_suffix;
				$int_username_length = strlen( $str_username );
			}
		}

		return $str_username;
	}
	
	// TODO: remove - generates password for user 

	function autogen_password ( $str_firstName, $str_lastName )
	{
		// simple
		$str_password = strtolower( $str_lastName . $str_firstName[0] );	
		
		return $str_password;
		
	}
	
}
?>
