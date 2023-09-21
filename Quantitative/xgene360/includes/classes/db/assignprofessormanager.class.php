<?php

class AssignProfessorManager
{
	var $m_obj_user = null;
	var $m_obj_db = null;

	/**
	 * Class Constructor
	 * PRE: $user_class is a valid user class enumerated in the id in the User table
	 * POST: TA object is constructed
	 * @param string $p_userId
	 */
	function AssignProfessorManager( $obj_user, $obj_db ) 
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
	function assign_professor( $str_prof_id, $int_course_id )
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
					$str_sql_query = "INSERT INTO XG_Teaches( professor_id, course_id) "
					               . "VALUES ( '" . $this->m_obj_db->format_sql_string( $str_prof_id ) . "', " 
							       . $this->m_obj_db->format_sql_string( $int_course_id ) . " )";
							       
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
					
					(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to assign " 
															. $this->m_obj_db->format_sql_string( $str_prof_id ) . " to the course " 
															. $this->m_obj_db->format_sql_string( $int_course_id ) 
															. ", either the course does not exist or user does not have permission for this operation" );
															
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
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to assign " 
												   . $this->m_obj_db->format_sql_string( $str_prof_id ) . " to the course " 
												   . $this->m_obj_db->format_sql_string( $int_course_id ) );
			return false;
		}

		if ( !$bln_success )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to assign " 
												   . $this->m_obj_db->format_sql_string( $str_prof_id ) . " to the course " 
												   . $this->m_obj_db->format_sql_string( $int_course_id ) 
												   . " due to database error" );
			return false;
		}
	
		(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " assigned " 
											   . $this->m_obj_db->format_sql_string( $str_prof_id ) . " to the course " 
											   . $this->m_obj_db->format_sql_string( $int_course_id ) );		
		return true;
	}

	/* Function Description
	 * PRE: 
	 * POST: 
	 * @param 
	 */
	function unassign_professor( $str_prof_id, $int_course_id ) 
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
					$str_sql_query = "DELETE FROM XG_Teaches "
									. "WHERE professor_id = '" . $this->m_obj_db->format_sql_string( $str_prof_id ) . "' "
									. "AND course_id = " . $this->m_obj_db->format_sql_string( $int_course_id );
									
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
					
					(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to unassign " 
														. $this->m_obj_db->format_sql_string( $str_prof_id ) . " from the course " 
														. $this->m_obj_db->format_sql_string( $int_course_id ) 
														. " due to database error" );
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
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to unassign " 
												   . $this->m_obj_db->format_sql_string( $str_prof_id ) . " from the course " 
												   . $this->m_obj_db->format_sql_string( $int_course_id ) );
			return false;
		}
		
		
		if ( !$bln_success )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to unassign " 
												   . $this->m_obj_db->format_sql_string( $str_prof_id ) . " from the course " 
												   . $this->m_obj_db->format_sql_string( $int_course_id ) 
												   . " due to database error" );
			return false;
		}

		(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " unassigned " 
											   . $this->m_obj_db->format_sql_string( $str_prof_id ) . " from the course " 
											   . $this->m_obj_db->format_sql_string( $int_course_id ) );
		return true;
	}
}

?>