<?php

class AssignTAManager
{
	var $m_obj_user = null;
	var $m_obj_db = null;

	/**
	 * Class Constructor
	 * PRE: $user_class is a valid user class enumerated in the id in the User table
	 * POST: TA object is constructed
	 * @param string $p_userId
	 */
	function __construct( $obj_user, $obj_db )
	{
	  	$this->m_obj_user = $obj_user;
		$this->m_obj_db = $obj_db;
	}

	/* Function Description
	 * PRE: 
	 * POST: 
	 * @param 
	 */
	/* Function Description
	 * PRE: 
	 * POST: 
	 * @param 
	 */
	function assign_TA( $str_ta_id, $int_course_id )
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
				
				$obj_course_manager_tmp = new CourseManager( $this->m_obj_user, $this->m_obj_db );
				$res_check_if_exists = $obj_course_manager_tmp->view_course_details( $int_course_id );

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
					$str_sql_query = "INSERT INTO XG_Assists(course_id, ta_id) "
					               . "VALUES ( " . $this->m_obj_db->format_sql_string( $int_course_id ) . ", '" 
							       . $this->m_obj_db->format_sql_string( $str_ta_id ) . "' )";
							       
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
					
					(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to assign ta " 
														   . $this->m_obj_db->format_sql_string( $str_ta_id ) . " to course " 
														   . $this->m_obj_db->format_sql_string( $int_course_id ) 
														   . ", either the ta or the course does not exists or user does not have permission for this operation" );
														   
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
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to assign ta " 
												   . $this->m_obj_db->format_sql_string( $str_ta_id ) . " to course " 
												   . $this->m_obj_db->format_sql_string( $int_course_id ) );	
			return false;
		}

		if ( !$bln_success )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to assign ta " 
												   . $this->m_obj_db->format_sql_string( $str_ta_id ) . " to course " 
												   . $this->m_obj_db->format_sql_string( $int_course_id ) 
												   . " due to database error" );
			return false;
		}

		(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " assigned ta " 
											   . $this->m_obj_db->format_sql_string( $str_ta_id ) . " to course " 
											   . $this->m_obj_db->format_sql_string( $int_course_id ) );
		return true;
	}

	/* Function Description
	 * PRE: 
	 * POST: 
	 * @param 
	 */
	function unassign_TA( $str_ta_id, $int_course_id ) 
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
				
				$obj_course_manager_tmp = new CourseManager( $this->m_obj_user, $this->m_obj_db );
				$res_check_if_exists = $obj_course_manager_tmp->view_course_details( $int_course_id );

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
					$str_sql_query = "DELETE FROM XG_Assists " 
								. "WHERE course_id = ". $this->m_obj_db->format_sql_string( $int_course_id ) . " "
								. "AND ta_id = '" . $this->m_obj_db->format_sql_string( $str_ta_id ) . "'";

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

					(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to unassign ta " 
														   . $this->m_obj_db->format_sql_string( $str_ta_id ) . " from course " 
														   . $this->m_obj_db->format_sql_string( $int_course_id ) 
														   . ", either the ta or the course does not exists or user does not have permission for this operation" );
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
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to unassign ta " 
												   . $this->m_obj_db->format_sql_string( $str_ta_id ) . " from course " 
												   . $this->m_obj_db->format_sql_string( $int_course_id ) );
			return false;
		}
		
		if ( !$bln_success )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to unassign ta " 
												   . $this->m_obj_db->format_sql_string( $str_ta_id ) . " from course " 
												   . $this->m_obj_db->format_sql_string( $int_course_id ) 
												   . " due to database error" );
			return false;
		}

		(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " unassigned ta " 
											   . $this->m_obj_db->format_sql_string( $str_ta_id ) . " from course " 
											   . $this->m_obj_db->format_sql_string( $int_course_id ) );
		return true;
	}
}

?>