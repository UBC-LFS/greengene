<?php

require_once( 'usermanager.class.php' );

class ProfessorManager extends UserManager
{
	var $m_obj_user = null;
	var $m_obj_db = null;

/**
 * Class Constructor
 * PRE: $obj_user is a user object from the cookie; 
 *      $obj_db is a DBManager object
 * POST: a ProfessorManager object has been created with the parameters
 * @param $obj_user, $obj_db
 */
	function ProfessorManager( $obj_user, $obj_db )
	{
		$this->m_obj_user = $obj_user;
		$this->m_obj_db = $obj_db;
	}

/**  Function: resource view_professors
*    ---------------------------------------------------------------- 
*    Purpose:           view the list of professors
*    Arguments:         none
*                       
*    Returns/Assigns:   resource or boolean or null
*/
	function view_professors()
	{
		$str_sql_query = null;
		$str_this_user = $this->m_obj_user->str_username;
	  
		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_PROFESSOR:
			case UP_ADMINISTRATOR:
			{
			    $str_sql_query = "SELECT U.UserId, U.FirstName, U.LastName "
							   . "FROM User U "
							   . "WHERE U.PrivilegeLvl = " . UP_PROFESSOR . " "
							   . "ORDER BY U.LastName, U.FirstName, U.UserId";
				break;
			}
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to view lists of professors " );
			return null;
		}

		(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " viewed lists of professors " );
		return $this->m_obj_db->query_select( $str_sql_query );
	}

/**  Function: resource view_professor_courses
*    ---------------------------------------------------------------- 
*    Purpose:           view the courses of a particular profesor that he/she is teaching
*    Arguments:         user id
*                       
*    Returns/Assigns:   resource or boolean or null
*/
	function view_professor_courses( $str_professor_id )
	{
		$str_sql_query = null;
		$str_this_user = $this->m_obj_user->str_username;
	  
		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_PROFESSOR:
			{
			    $str_sql_query = "SELECT C.CourseId, C.Name, C.Description "
							   . "FROM User U, Course C, XG_Teaches T, User Current, XG_Teaches T2 "
							   . "WHERE C.CourseId = T.course_id "
							   . "AND T.professor_id = U.UserId "
							   . "AND T2.professor_id = Current.UserId "
							   . "AND T.course_id = T2.course_id "
							   . "AND U.UserId = '". $this->m_obj_db->format_sql_string( $str_professor_id ) ."' "
							   . "AND Current.UserId = '" . $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "' "
							   . "ORDER BY C.Name, C.Description";
				break;
			}
			case UP_ADMINISTRATOR:
			{			
			    $str_sql_query = "SELECT C.CourseId, C.Name, C.Description "
							   . "FROM User U, Course C, XG_Teaches T "
							   . "WHERE C.CourseId = T.course_id "
							   . "AND T.professor_id = U.UserId "
							   . "AND U.UserId = '". $this->m_obj_db->format_sql_string( $str_professor_id ) ."' "
							   . "ORDER BY C.Name, C.Description";
			   break;
			}
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to view courses associated with professor " 
												   . $this->m_obj_db->format_sql_string( $str_professor_id ) );
			return null;
		}

		(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " viewed courses associated with professor " 
											   . $this->m_obj_db->format_sql_string( $str_professor_id ) );
		return $this->m_obj_db->query_select( $str_sql_query );
	}
}

?>