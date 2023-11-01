<?php

require_once( 'usermanager.class.php' );

class TAManager extends UserManager
{
	var $m_obj_user = null;
	var $m_obj_db = null;
	
/**
 * Class Constructor
 * PRE: $obj_user is a user object from the cookie; 
 *      $obj_db is a DBManager object
 * POST: a TAManager object has been created with the parameters
 * @param $obj_user, $obj_db
 */
	function __construct( $obj_user, $obj_db )
	{
	    $this->m_obj_user = $obj_user;
		$this->m_obj_db = $obj_db;
	}

/**  Function: resource view_tas
*    ---------------------------------------------------------------- 
*    Purpose:           views a list of tas
*    Arguments:         none
*                       
*    Returns/Assigns:   resource or boolean or null
*/
	function view_tas() 
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
                               . "WHERE U.PrivilegeLvl = " . UP_TA . " "
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
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to view a list of tas " );
			return null;
		}

		(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " viewed a list of tas " );
		return $this->m_obj_db->query_select( $str_sql_query );
	}

/**  Function: resource view_ta_courses
*    ---------------------------------------------------------------- 
*    Purpose:           view the courses of a particular ta that he/she is assisting
*    Arguments:         user id
*                       
*    Returns/Assigns:   resource or boolean or null
*/
	function view_ta_courses( $str_ta_id )
	{
		$str_sql_query = null;
		$str_this_user = $this->m_obj_user->str_username;
	  
		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_PROFESSOR:
			case UP_ADMINISTRATOR:
			{
			    $str_sql_query = "SELECT C.CourseId, C.Name, C.Description "
			                   . "FROM Course C, User U, XG_Assists A "
			                   . "WHERE A.course_id = C.CourseId "
			                   . "AND A.ta_id = U.UserId "
			                   . "AND U.UserId = '" . $this->m_obj_db->format_sql_string( $str_ta_id ) . "' "
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
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to view a list of courses associated with ta " 
												   . $this->m_obj_db->format_sql_string( $str_ta_id ) );
			return null;
		}

		(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " viewed a list of courses associated with ta " 
											   . $this->m_obj_db->format_sql_string( $str_ta_id ) );	  
		return $this->m_obj_db->query_select( $str_sql_query );
	}
}

?>