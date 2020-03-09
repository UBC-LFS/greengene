<?php

class SolutionManager
{
	var $m_obj_user = null;
	var $m_obj_db = null;

/**
 * Class Constructor
 * PRE: $obj_user is a user object from the cookie; 
 *      $obj_db is a DBManager object
 * POST: a SolutionManager object has been created with the parameters
 * @param $obj_user, $obj_db
 */
	function SolutionManager( $obj_user, $obj_db )
	{
	  	$this->m_obj_user = $obj_user;
		$this->m_obj_db = $obj_db;
	}

	function view_problem_solution( $int_problem_id )
	{
		$str_sql_query = null;
		$str_this_user = $this->m_obj_user->str_username;
		
		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_TA:    
			case UP_PROFESSOR:
			case UP_ADMINISTRATOR:
			
				$str_sql_query = "SELECT P.problem_id, P.trait_A_number_of_genes, "
			                   . "P.trait_B_number_of_genes, P.trait_A_h2, P.trait_B_h2, P.trait_A_var, P.trait_B_var, "
			                   . "P.trait_A_parent_A_mean, P.trait_A_parent_B_mean, P.trait_B_parent_A_mean, P.trait_B_parent_B_mean "
			                   . "FROM XG_ProblemTraitMadeFor P "
			                   . "WHERE P.problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id );

			    break;
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to view the solution for problem " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
			return null;
		}

		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " viewed the solution for problem " 
											   . $this->m_obj_db->format_sql_string( $int_problem_id ) );	  
		return $this->m_obj_db->query_select( $str_sql_query );
	}


	function view_student_solutions( $int_problem_id ) 
	{
		$str_sql_query = null;
		$str_this_user = $this->m_obj_user->str_username;
	  
		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_TA:    
			case UP_PROFESSOR:
			case UP_ADMINISTRATOR:

				$str_sql_query = "SELECT S.problem_id, U.StudentNum, U.LastName, U.FirstName, U.UserId, UNIX_TIMESTAMP( S.hand_in_timestamp ) AS hand_in_date, "
				               . "S.solution_desc, S.trait_A_number_of_genes, "
			                   . "S.trait_B_number_of_genes, S.trait_A_h2, S.trait_B_h2, S.trait_A_var, S.trait_B_var, "
			                   . "S.trait_A_parent_A_mean, S.trait_A_parent_B_mean, S.trait_B_parent_A_mean, S.trait_B_parent_B_mean "
			                   . "FROM XG_ProblemSolution S, User U "
			                   . "WHERE U.UserId = S.student_id "
			                   . "AND S.problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id );

			    break;
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to view the solutions of the students for problem " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
			return null;
		}

		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " viewed the solutions of the students for problem " 
											   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
	  
		return $this->m_obj_db->query_select( $str_sql_query );
	}
 
 	function view_student_solution( $str_student_id, $int_problem_id ) 
	{
		$str_sql_query = null;
		$str_this_user = $this->m_obj_user->str_username;
	  
		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_TA:    
			case UP_PROFESSOR:
			case UP_ADMINISTRATOR:

				$str_sql_query = "SELECT S.problem_id, U.StudentNum, U.LastName, U.FirstName, UNIX_TIMESTAMP( S.hand_in_timestamp ) AS hand_in_date, "
				               . "S.solution_desc, S.trait_A_number_of_genes, "
			                   . "S.trait_B_number_of_genes, S.trait_A_h2, S.trait_B_h2, S.trait_A_var, S.trait_B_var, "
			                   . "S.trait_A_parent_A_mean, S.trait_A_parent_B_mean, S.trait_B_parent_A_mean, S.trait_B_parent_B_mean "
			                   . "FROM XG_ProblemSolution S, User U "
			                   . "WHERE U.UserId = S.student_id "
			                   . "AND S.problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id ) . " "
			                   . "AND U.UserId = '" . $this->m_obj_db->format_sql_string( $str_student_id ) ."'";

			    break;
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to view the solution of student "
												   . $this->m_obj_db->format_sql_string( $str_student_id )
												   . "for problem " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
			return null;
		}

		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " viewed the solution of student "
											   . $this->m_obj_db->format_sql_string( $str_student_id )
											   . " for problem " 
											   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
	  
		return $this->m_obj_db->query_select( $str_sql_query );
	}
 
 
	function add_solution( $int_problem_id, $str_solution_desc, $int_trait_A_number_of_genes, 
						   $int_trait_B_number_of_genes, $dbl_trait_A_h2,
	                       $dbl_trait_B_h2, $dbl_trait_A_var, $dbl_trait_B_var, $dbl_trait_A_parent_A_mean,
	                       $dbl_trait_A_parent_B_mean, $dbl_trait_B_parent_A_mean, $dbl_trait_B_parent_B_mean )
	{
		$str_sql_query = null;
		$res_check_if_exists = null;
		$str_this_user = $this->m_obj_user->str_username;
		$bln_success = true;
	  
		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_STUDENT:
  			{
				$str_sql_query = "BEGIN";

				if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
				{
					$bln_success = false;
					break;
				}

				$obj_problem_manager_tmp = new ProblemManager( $this->m_obj_user, $this->m_obj_db );
				$res_check_if_exists = $obj_problem_manager_tmp->view_problem_details( $int_problem_id );

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
			 		$str_sql_query = "SELECT NOW() AS time";
					
					$res_date = $this->m_obj_db->query_select( $str_sql_query );
					
					if ( $res_date == null )
					{
						$str_sql_query = null;
						break;
					}

					if ( $res_date == false )
					{
						$bln_success = false;
						break;
					}
					
					$res_row = $this->m_obj_db->fetch( $res_date );
					$dat_hand_in_date = $res_row->time;
					
					// remove the old one if there is one
					
					$str_sql_query = "DELETE FROM XG_ProblemSolution WHERE student_id = '"
								. $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "' "
								. "AND problem_id = "
								. $this->m_obj_db->format_sql_string( $int_problem_id );
								   
					if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
					{
						$bln_success = false;
						break;
					}
	  
					$str_sql_query = "INSERT INTO XG_ProblemSolution ( problem_id, student_id, hand_in_timestamp, solution_desc, "
								. "trait_A_number_of_genes, trait_B_number_of_genes, trait_A_h2, trait_B_h2, trait_A_var, " 
								. "trait_B_var, trait_A_parent_A_mean, trait_A_parent_B_mean, trait_B_parent_A_mean, "
								. "trait_B_parent_B_mean ) VALUES ("
								. $this->m_obj_db->format_sql_string( $int_problem_id ) . ", '"
								. $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) ."', '"
								. $this->m_obj_db->format_sql_string( $dat_hand_in_date ) . "', '"
								. $this->m_obj_db->format_sql_string( $str_solution_desc ) . "', "
								. $this->m_obj_db->format_sql_string( $int_trait_A_number_of_genes ) . ", "
								. $this->m_obj_db->format_sql_string( $int_trait_B_number_of_genes ) . ", "
								. $this->m_obj_db->format_sql_string( $dbl_trait_A_h2 ) . ", "
								. $this->m_obj_db->format_sql_string( $dbl_trait_B_h2 ) . ", "
								. $this->m_obj_db->format_sql_string( $dbl_trait_A_var ) . ", "
								. $this->m_obj_db->format_sql_string( $dbl_trait_B_var ) . ", "
								. $this->m_obj_db->format_sql_string( $dbl_trait_A_parent_A_mean ) . ", "
								. $this->m_obj_db->format_sql_string( $dbl_trait_A_parent_B_mean ) . ", "
								. $this->m_obj_db->format_sql_string( $dbl_trait_B_parent_A_mean ) . ", "
								. $this->m_obj_db->format_sql_string( $dbl_trait_B_parent_B_mean ) . ")";
								
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
					
					Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to add solution for problem " 
														   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
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
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to add solution for problem " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
			return false;
		}

		if ( !$bln_success )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to add solution for problem " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
			return false;
		}

		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " added solution for problem " 
											   . $this->m_obj_db->format_sql_string( $int_problem_id ) );	  
		return true;
	}
}

?>