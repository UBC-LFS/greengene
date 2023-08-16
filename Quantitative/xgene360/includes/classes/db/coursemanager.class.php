<?php

class CourseManager
{
	var $m_obj_user = null;
	var $m_obj_db = null;
  
/**
 * Class Constructor
 * PRE: $obj_user is a user object from the cookie; 
 *      $obj_db is a DBManager object
 * POST: a CourseManager object has been created with the parameters
 * @param $obj_user, $obj_db
 */
	function CourseManager( $obj_user, $obj_db )
	{
	    $this->m_obj_user = $obj_user;
		$this->m_obj_db = $obj_db;
	}

/**  Function: resource view_courses
*    ---------------------------------------------------------------- 
*    Purpose:           views a list of courses
*    Arguments:         none
*                       
*    Returns/Assigns:   resource or boolean or null
*/
	function view_courses()
	{
		$str_sql_query = null;
		$str_this_user = $this->m_obj_user->str_username;
	  
		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_TA:
			{
				$str_sql_query = "SELECT C.CourseId, C.Name, C.Description "
							   . "FROM Course C, XG_Assists A, User U "
							   . "WHERE C.CourseID = A.course_id "
							   . "AND A.ta_id = U.UserID "
							   . "AND U.PrivilegeLvl = " . UP_TA . " "
							   . "AND U.UserID = '". $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "' "
                               . "ORDER BY C.Name";
			   break;
			}  
			case UP_PROFESSOR:
			{
			      $str_sql_query = "SELECT C.CourseId, C.Name, C.Description "
								 . "FROM Course C, XG_Teaches T, User U "
								 . "WHERE C.CourseId = T.course_id "
								 . "AND T.professor_id = U.UserID "
								 . "AND U.PrivilegeLvl = " . UP_PROFESSOR . " "
								 . "AND U.UserID = '". $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "' "
								 . "ORDER BY C.Name";
				  break;
			}				
			case UP_ADMINISTRATOR:
			{
			    $str_sql_query = "SELECT C.CourseId, C.Name, C.Description "
							    . "FROM Course C "
							    . "ORDER BY C.Name";
			   break;
			}
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to view a list of courses " );
			return null;
		}
	  
		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " viewed a list of courses " );
		return $this->m_obj_db->query_select( $str_sql_query );
	}
	
/**  Function: arr_course_ids_names course_names_list
*    ---------------------------------------------------------------- 
*    Purpose:           translates the course id to course names for the front end 
*    Arguments:         an array of course ids
*                       
*    Returns/Assigns:   an array of arrays of (course id, course name)
*/
	function course_names_list( $arr_course_id )
	{
		$arr_course_ids_names = array();
		
		for( $i = 0; $i < count( $arr_course_id ); $i++ )
		{
			$res_course = $this->view_course_details( $arr_course_id[$i]);
			$res_row = $this->m_obj_db->fetch( $res_course );

			if ( $res_row != null || $res_row != false )
			{
				$arr_course_id_name = array();
				array_push( $arr_course_id_name, $res_row->CourseId );
				array_push( $arr_course_id_name, $res_row->Name );

				array_push( $arr_course_ids_names, $arr_course_id_name );
			}
		}
		
		return $arr_course_ids_names; 
	}

/**  Function: resource view_course_details
*    ---------------------------------------------------------------- 
*    Purpose:           views a particular course detail
*    Arguments:         course id
*                       
*    Returns/Assigns:   resource or false or null
*/
	function view_course_details( $int_course_id )
	{
		$str_sql_query = null;
		$str_this_user = $this->m_obj_user->str_username;
		
		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_TA:
			{
				$str_sql_query = "SELECT C.CourseId, C.Name, C.Description "
				               . "FROM Course C, User U, XG_Assists A "
				               . "WHERE A.course_id = C.CourseId "
				               . "AND A.ta_id = U.UserId "
				               . "AND U.UserId = '" . $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "' "
				               . "AND C.CourseId = " . $this->m_obj_db->format_sql_string( $int_course_id );
				break;
			}	
			case UP_PROFESSOR:
			{	
				$str_sql_query = "SELECT C.CourseId, C.Name, C.Description "
				               . "FROM Course C, User U, XG_Teaches T "
				               . "WHERE T.course_id = C.CourseId "
				               . "AND T.professor_id = U.UserId "
				               . "AND U.UserId = '" . $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "' "
				               . "AND C.CourseId = " . $this->m_obj_db->format_sql_string( $int_course_id );
				break;
			}
			case UP_ADMINISTRATOR:
			{   
			   $str_sql_query = "SELECT C.CourseId, C.Name, C.Description "
			                   . "FROM Course C "
			                   . "WHERE C.CourseId = ". $this->m_obj_db->format_sql_string( $int_course_id );
			   break;
			}
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to view course details of " 
												   . $this->m_obj_db->format_sql_string( $int_course_id ) );
			return null;
		}

		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " viewed course details of " 
											   . $this->m_obj_db->format_sql_string( $int_course_id ) );
		return $this->m_obj_db->query_select( $str_sql_query );
	}

/**  Function: resource view_course_professors
*    ---------------------------------------------------------------- 
*    Purpose:           view the professors that is teaching this course
*    Arguments:         course id
*                       
*    Returns/Assigns:   resource or boolean or null
*/
	function view_course_professors( $int_course_id )
	{
		$str_sql_query = null;
		$str_this_user = $this->m_obj_user->str_username;
	  
		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_TA:
			{
				$str_sql_query = "SELECT U.UserId, U.FirstName, U.LastName "
			                   . "FROM Course C, User U, XG_Teaches T, User Current, XG_Assists A "
			                   . "WHERE U.UserId = T.professor_id "
			                   . "AND Current.UserId = A.ta_id "
			                   . "AND A.course_id = C.CourseId "
			                   . "AND T.course_id = C.CourseId "
			                   . "AND C.CourseId = ". $this->m_obj_db->format_sql_string( $int_course_id ) . " " 
			                   . "AND Current.UserId = '" . $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "' ";
				break;
			}
			case UP_PROFESSOR:
			{
				$str_sql_query = "SELECT U.UserId, U.FirstName, U.LastName "
			                   . "FROM Course C, User U, XG_Teaches T, User Current, XG_Teaches T2 "
			                   . "WHERE U.UserId = T.professor_id "
			                   . "AND T.course_id = C.CourseId "
			                   . "AND T2.course_id = C.CourseId "
			                   . "AND Current.UserId = T2.professor_id "
			                   . "AND C.CourseId = ". $this->m_obj_db->format_sql_string( $int_course_id ) . " " 
			                   . "AND Current.UserId = '" . $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "' ";
				break;
			}
			case UP_ADMINISTRATOR:
			{  
			    $str_sql_query = "SELECT U.UserId, U.FirstName, U.LastName "
			                   . "FROM Course C, User U, XG_Teaches T "
			                   . "WHERE U.UserId = T.professor_id "
			                   . "AND T.course_id = C.CourseId "
			                   . "AND C.CourseId = ". $this->m_obj_db->format_sql_string( $int_course_id );
			   break;
			}
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to view professors associated with the course " 
												   . $this->m_obj_db->format_sql_string( $int_course_id ) );
			return null;
		}

		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " viewed professors associated with the course " 
											   . $this->m_obj_db->format_sql_string( $int_course_id ) );
		return $this->m_obj_db->query_select( $str_sql_query );
	}	
	
/**  Function: resource view_course_tas
*    ---------------------------------------------------------------- 
*    Purpose:           view the tas that is assisting this course
*    Arguments:         course id
*                       
*    Returns/Assigns:   resource or boolean or null
*/
	function view_course_tas( $int_course_id )
	{
		$str_sql_query = null;
		$str_this_user = $this->m_obj_user->str_username;
	  
		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_TA:
			{
			    $str_sql_query = "SELECT U.UserId, U.FirstName, U.LastName "
			                   . "FROM Course C, User U, XG_Assists A, User Current, XG_Assists A2 "
			                   . "WHERE U.UserId = A.ta_id "
			                   . "AND A.course_id = C.CourseId "
			                   . "AND Current.UserId = A2.ta_id "
			                   . "AND A2.course_id = C.CourseId "
			                   . "AND C.CourseId = ". $this->m_obj_db->format_sql_string( $int_course_id ) . " "
			                   . "AND Current.UserId = '" . $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "' ";
			   break;
			}
			
			case UP_PROFESSOR:
			{
			    $str_sql_query = "SELECT U.UserId, U.FirstName, U.LastName "
			                   . "FROM Course C, User U, XG_Assists A, User Current, XG_Teaches T "
			                   . "WHERE U.UserId = A.ta_id "
			                   . "AND A.course_id = C.CourseId "
			                   . "AND C.CourseId = ". $this->m_obj_db->format_sql_string( $int_course_id ) . " "
			                   . "AND T.professor_id = Current.UserId "
			                   . "AND T.course_id = C.CourseId "
			                   . "AND Current.UserId = '" . $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "' ";
			   break;
			}
			   
			case UP_ADMINISTRATOR:
			 {  
			    $str_sql_query = "SELECT U.UserId, U.FirstName, U.LastName "
			                   . " FROM Course C, User U, XG_Assists A "
			                   . " WHERE U.UserId = A.ta_id "
			                   . " AND A.course_id = C.CourseId "
			                   . " AND C.CourseId = ". $this->m_obj_db->format_sql_string( $int_course_id );
			   break;
			}
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to view tas associated with the course " 
												   . $this->m_obj_db->format_sql_string( $int_course_id ) );
			return null;
		}
	  
		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " viewed tas associated with the course " 
											   . $this->m_obj_db->format_sql_string( $int_course_id ) );	  
		return $this->m_obj_db->query_select( $str_sql_query );
	}	
	
/**  Function: resource view_course_problems
*    ---------------------------------------------------------------- 
*    Purpose:           view the problems that is associated with this course
*    Arguments:         course id
*                       
*    Returns/Assigns:   resource or boolean or null
*/
	function view_course_problems( $int_course_id )
	{
		$str_sql_query = null;
		$str_this_user = $this->m_obj_user->str_username;
	  
		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_TA:
			{
				$str_sql_query = "SELECT P.problem_id, P.problem_name, "
							   . "UNIX_TIMESTAMP( P.start_date ) AS start_date, "
							   . "UNIX_TIMESTAMP( P.due_date ) AS due_date "
			                   . "FROM Course C, XG_ProblemTraitMadeFor P, XG_Assists A, User U "
			                   . "WHERE P.course_id = C.CourseId "
			                   . "AND U.UserId = A.ta_id "
			                   . "AND A.course_id = C.CourseId "
			                   . "AND C.CourseId = ". $this->m_obj_db->format_sql_string( $int_course_id ) . " "
			                   . "AND U.UserId = '" . $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "'";
				break;
			}
				
			case UP_PROFESSOR:
			{
				$str_sql_query = "SELECT P.problem_id, P.problem_name, "
							   . "UNIX_TIMESTAMP( P.start_date ) AS start_date, "
							   . "UNIX_TIMESTAMP( P.due_date ) AS due_date "
			                   . "FROM Course C, XG_ProblemTraitMadeFor P, XG_Teaches T, User U "
			                   . "WHERE P.course_id = C.CourseId "
			                   . "AND U.UserId = T.professor_id "
			                   . "AND T.course_id = C.CourseId "
			                   . "AND C.CourseId = ". $this->m_obj_db->format_sql_string( $int_course_id ) . " "
			                   . "AND U.UserId = '" . $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "'";
				break;
			}
				
			case UP_ADMINISTRATOR:
			{
			    $str_sql_query = "SELECT P.problem_id, P.problem_name, "
							   . "UNIX_TIMESTAMP(P.start_date) AS start_date, "
							   . "UNIX_TIMESTAMP(P.due_date) AS due_date "
			                   . "FROM Course C, XG_ProblemTraitMadeFor P "
			                   . "WHERE P.course_id = C.CourseId "
			                   . "AND C.CourseId = ". $this->m_obj_db->format_sql_string( $int_course_id );
			   break;
			}
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to view problems associated with the course " 
												   . $this->m_obj_db->format_sql_string( $int_course_id ) );
			return null;
		}

		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " viewed problems associated with the course " 
											   . $this->m_obj_db->format_sql_string( $int_course_id ) );
		return $this->m_obj_db->query_select( $str_sql_query );
	}		

/**  Function: boolean add_course
*    ---------------------------------------------------------------- 
*    Purpose:           create a new course
*    Arguments:         course name, course description
*                       
*    Returns/Assigns:   boolean or null
*/
	function add_course( $str_course_name, $str_course_description )
	{
		$str_sql_query = null;
		$res_check_if_exists = null;
		$str_this_user = $this->m_obj_user->str_username;
		$bln_success = true;
	  
		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_PROFESSOR:
			{
				$str_sql_query = "BEGIN";

				if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
				{
					$bln_success = false;
					break;
				}
				
				$str_sql_query = "INSERT INTO Course( Name, Description ) "
							   . "VALUES ( '" . $this->m_obj_db->format_sql_string( $str_course_name ) . "', '" 
							   . $this->m_obj_db->format_sql_string( $str_course_description ) . "' )";

				if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
				{
					$bln_success = false;
					break;
				}

				$int_course_id = $this->m_obj_db->get_last_inserted_id( );

				$str_sql_query = "INSERT INTO XG_Teaches( professor_id, course_id ) "
							   . "VALUES ( '" . $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "', " 
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
				break;
			}

			case UP_ADMINISTRATOR:
			{
				$str_sql_query = "BEGIN";

				if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
				{
					$bln_success = false;
					break;
				}
				
				$str_sql_query = "INSERT INTO Course( Name, Description ) "
							   . "VALUES ( '" . $this->m_obj_db->format_sql_string( $str_course_name ) . "', '" 
							   . $this->m_obj_db->format_sql_string( $str_course_description ) . "' )";

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
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to create course with " 
												   . $this->m_obj_db->format_sql_string( $str_course_name ) . ", "
												   . $this->m_obj_db->format_sql_string( $str_course_description ) );
			return false;
		}

		if ( !$bln_success )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to create course with " 
												   . $this->m_obj_db->format_sql_string( $str_course_name ) . ", "
												   . $this->m_obj_db->format_sql_string( $str_course_description ) );
			return false;
		}
		
		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " created a course with " 
											   . $this->m_obj_db->format_sql_string( $str_course_name ) . ", "
											   . $this->m_obj_db->format_sql_string( $str_course_description ) );
		return true;
	}

/**  Function: boolean delete_course
*    ---------------------------------------------------------------- 
*    Purpose:           delete a course
*    Arguments:         course id
*                       
*    Returns/Assigns:   boolean or null
*/
	function delete_course( $int_course_id )
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

				$res_check_if_exists = $this->view_course_details( $int_course_id );

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
					$str_sql_query = "DELETE FROM Course "
							       . "WHERE CourseId = ". $this->m_obj_db->format_sql_string( $int_course_id );

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
					
					Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " does not have permission to delete course " 
														   . $this->m_obj_db->format_sql_string( $int_course_id ) 
														   . " or course " 
														   . $this->m_obj_db->format_sql_string( $int_course_id ) 
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
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to delete course " 
												   . $this->m_obj_db->format_sql_string( $int_course_id ) );
			return false;
		}
		
		if ( !$bln_success )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to delete course " 
												   . $this->m_obj_db->format_sql_string( $int_course_id ) 
												   . " due to database error" );
			return false;
		}

		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " deleted course " 
											   . $this->m_obj_db->format_sql_string( $int_course_id ) );
		return true;
		
	}

/**  Function: boolean modify_course
*    ---------------------------------------------------------------- 
*    Purpose:           modify the details of a course
*    Arguments:         course id, course name, course desc
*                       
*    Returns/Assigns:   boolean or null
*/
	function modify_course( $int_course_id, $str_course_name, $str_course_description )
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

				$res_check_if_exists = $this->view_course_details( $int_course_id );

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
					$str_sql_query = "UPDATE Course "
							       . "SET Name = '". $this->m_obj_db->format_sql_string( $str_course_name ) . "', "
							       . "Description = '" . $this->m_obj_db->format_sql_string( $str_course_description ) . "' "
							       . "WHERE CourseId = " . $this->m_obj_db->format_sql_string( $int_course_id );

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
					
					Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " does not have permission to modify course " 
														   . $this->m_obj_db->format_sql_string( $int_course_id ) . " with " 
														   . $this->m_obj_db->format_sql_string( $str_course_name ) . ", " 
														   . $this->m_obj_db->format_sql_string( $str_course_description ) 
														   . " or course does not exist");
					return false;
				}
			}

				break;	    
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to modify course " 
												   . $this->m_obj_db->format_sql_string( $int_course_id ) . " with " 
												   . $this->m_obj_db->format_sql_string( $str_course_name ) . ", " 
												   . $this->m_obj_db->format_sql_string( $str_course_description ) );
			return false;
		}
  
		if ( !$bln_success )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to modify course " 
												   . $this->m_obj_db->format_sql_string( $int_course_id ) . " with " 
												   . $this->m_obj_db->format_sql_string( $str_course_name ) . ", " 
												   . $this->m_obj_db->format_sql_string( $str_course_description ) 
												   . " due to database error");
			return false;
		}

		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " modified course " 
											   . $this->m_obj_db->format_sql_string( $int_course_id ) . " with " 
											   . $this->m_obj_db->format_sql_string( $str_course_name ) . ", " 
											   . $this->m_obj_db->format_sql_string( $str_course_description ) );
		return true;
  }
}
  
?>