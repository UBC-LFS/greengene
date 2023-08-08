<?php

require_once( 'usermanager.class.php' );

class StudentManager extends UserManager
{
	var $m_obj_user = null;
	var $m_obj_db = null;

/**
 * Class Constructor
 * PRE: $obj_user is a user object from the cookie; 
 *      $obj_db is a DBManager object
 * POST: a StudentManager object has been created with the parameters
 * @param $obj_user, $obj_db
 */
	// function StudentManager( $obj_user, $obj_db )
	function __construct( $obj_user, $obj_db ) 
	{
	    $this->m_obj_user = $obj_user;
		$this->m_obj_db = $obj_db;
	}

/**  Function: resource view_students
*    ---------------------------------------------------------------- 
*    Purpose:           view the list of students
*    Arguments:         none
*                       
*    Returns/Assigns:   resource or boolean or null
*/
	function view_students() 
	{
		$str_sql_query = null;
		$str_this_user = $this->m_obj_user->str_username;
	  
		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_TA:				// TA sees only students which are assigned to courses he assists // need to check
			{
				$str_sql_query = "SELECT DISTINCT student.UserId, student.FirstName, student.LastName, student.StudentNum "
							   . "FROM User TA, User student, XG_Assists Ast, Course C, "
							   . "XG_ProblemTraitMadeFor P, XG_Assigns Asn "
							   . "WHERE TA.PrivilegeLvl = " . UP_TA . " "
							   . "AND TA.UserId = Ast.ta_id "
							   . "AND Ast.course_id = C.CourseId "
							   . "AND C.CourseId = P.course_id "
							   . "AND Asn.problem_id = P.problem_id "
							   . "AND Asn.student_id = student.UserId "
							   . "AND TA.UserId = '" . $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "' "
							   . "ORDER BY student.LastName, student.FirstName, student.UserId"; 
				break;
			}	
			case UP_PROFESSOR:		// Professors and the admin see ALL students
			case UP_ADMINISTRATOR:
			{
			    $str_sql_query = "SELECT U.UserId, U.FirstName, U.LastName, U.StudentNum "
							   . "FROM User U "
							   . "WHERE PrivilegeLvl = " . UP_STUDENT . " "
							   . "ORDER BY U.LastName, U.FirstName, U.StudentNum, U.UserId";
				break;
			}
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to view a list of students " );
			return null;
		}

		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " viewed a list of students " );
		return $this->m_obj_db->query_select( $str_sql_query );
	}

/**  Function: resource view_student_problems
*    ---------------------------------------------------------------- 
*    Purpose:           view the problems that a particular student is assigned to
*    Arguments:         user id
*                       
*    Returns/Assigns:   resource or boolean or null
*/
	function view_student_problems( $str_student_id )
	{
		$str_sql_query = null;
		$str_this_user = $this->m_obj_user->str_username;
	  
		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_TA:				// TA sees only students which are assigned to courses he assists
			{
				$str_sql_query = "SELECT P.problem_id, P.problem_name, C.Name, "
							   . "UNIX_TIMESTAMP( P.start_date ) AS start_date, "
							   . "UNIX_TIMESTAMP( P.due_date ) AS due_date "
			                   . "FROM User U, XG_Assigns A, XG_ProblemTraitMadeFor P, Course C, User Current, XG_Assists TA "
			                   . "WHERE P.problem_id = A.problem_id "
			                   . "AND C.CourseId = P.course_id "
			                   . "AND A.student_id = U.UserId "
			                   . "AND U.UserId = '" . $this->m_obj_db->format_sql_string( $str_student_id ) . "' "
			                   . "AND Current.UserId = '" . $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "' "
			                   . "AND Current.UserId = TA.ta_id "
			                   . "AND TA.course_id = C.CourseId "
			                   . "ORDER BY P.problem_name";
				break;
			
			}
			case UP_PROFESSOR:		// Professors and the admin see ALL students
			case UP_ADMINISTRATOR:
			{
			    $str_sql_query = "SELECT P.problem_id, P.problem_name, C.Name, "
							   . "UNIX_TIMESTAMP( P.start_date ) AS start_date, "
							   . "UNIX_TIMESTAMP( P.due_date ) AS due_date "
			                   . "FROM User U, XG_Assigns A, XG_ProblemTraitMadeFor P, Course C "
			                   . "WHERE P.problem_id = A.problem_id "
			                   . "AND C.CourseId = P.course_id "
			                   . "AND A.student_id = U.UserId "
			                   . "AND U.UserId = '" . $this->m_obj_db->format_sql_string( $str_student_id ) . "' "
			                   . "ORDER BY P.problem_name";
				break;
			}
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to view problems associated with student " 
												   . $this->m_obj_db->format_sql_string( $str_student_id ) );
			return null;
		}

		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " viewed problems associated with student " 
											   . $this->m_obj_db->format_sql_string( $str_student_id ) );	  
		return $this->m_obj_db->query_select( $str_sql_query );
	}
}

?>