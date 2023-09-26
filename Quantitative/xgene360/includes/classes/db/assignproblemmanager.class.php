<?php

class AssignProblemManager
{
	var $m_obj_user = null;
	var $m_obj_db = null;

/**
 * Class Constructor
 * PRE: $obj_user is a user object from the cookie; 
 *      $obj_db is a DBManager object
 * POST: a AssignProblemManager object has been created with the parameters
 * @param $obj_user, $obj_db
 */
	function __construct( $obj_user, $obj_db )
	{
	  	$this->m_obj_user = $obj_user;
		$this->m_obj_db = $obj_db;
	}
	
	/**
	 * View students assigned to a problem
	 * PRE: a valid problem id is given
	 * POST: a  valid resource is returned, or null on error
	 * @return Resource containing database query results, or null on error
	 */
	function view_students_assigned_to_problem( $int_problem_id ) 
	{
		$str_sql_query = null;
		$str_this_user = $this->m_obj_user->str_username;
	  
		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_TA:
			{
				$str_sql_query = "SELECT U.UserId, U.FirstName, U.LastName, U.StudentNum "
							   . "FROM User U, XG_Assigns A, XG_Assists Ass, User Current, XG_ProblemTraitMadeFor P "
							   . "WHERE U.PrivilegeLvl = " . UP_STUDENT . " "
							   . "AND A.problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id ) . " "
							   . "AND P.problem_id = A.problem_id "
							   . "AND U.UserId = A.student_id "
							   . "AND P.course_id = Ass.course_id "
							   . "AND Current.UserId = Ass.ta_id "
							   . "AND Current.UserId = '" . $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "' "
							   . "ORDER BY U.LastName, U.FirstName, U.StudentNum, U.UserId";
				break;
			}
			
			case UP_PROFESSOR:
				
				$str_sql_query = "SELECT U.UserId, U.FirstName, U.LastName, U.StudentNum "
							   . "FROM User U, XG_Assigns A, XG_Teaches T, User Current, XG_ProblemTraitMadeFor P "
							   . "WHERE U.PrivilegeLvl = " . UP_STUDENT . " "
							   . "AND A.problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id ) . " "
							   . "AND P.problem_id = A.problem_id "
							   . "AND U.UserId = A.student_id "
							   . "AND P.course_id = T.course_id "
							   . "AND Current.UserId = T.professor_id "
							   . "AND Current.UserId = '" . $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "' "
							   . "ORDER BY U.LastName, U.FirstName, U.StudentNum, U.UserId";
				break;
			
			case UP_ADMINISTRATOR:
			
			    $str_sql_query = "SELECT U.UserId, U.FirstName, U.LastName, U.StudentNum "
							   . "FROM User U, XG_Assigns A "
							   . "WHERE U.PrivilegeLvl = " . UP_STUDENT . " "
									. "AND " . $int_problem_id . " = A.problem_id "
									. "AND U.UserId = A.student_id "
							   . "ORDER BY U.LastName, U.FirstName, U.StudentNum, U.UserId";
				break;
		}
		
		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to view students associated with the problem " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
			return null;
		}

		(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " viewed students associated with the problem " 
											   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
		return $this->m_obj_db->query_select( $str_sql_query );
	}
	
	
}

?>