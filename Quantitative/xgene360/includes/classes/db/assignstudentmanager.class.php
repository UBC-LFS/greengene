<?php

class AssignStudentManager
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
 
	function assign_student_to_problem( $str_student_id, $int_problem_id )
	{
		$str_sql_query = null;
		$res_check_if_exists = null;
		$str_this_user = $this->m_obj_user->str_username;
		$bln_success = true;

		// assign the problem	  
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

				$obj_problem_manager = new ProblemManager( $this->m_obj_user, $this->m_obj_db );
				$res_check_if_exists = $obj_problem_manager->view_problem_details( $int_problem_id );

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
					$str_sql_query = "INSERT INTO XG_Assigns(problem_id, student_id) "
									. "VALUES ( " . $this->m_obj_db->format_sql_string( $int_problem_id ) . ", '"
									. $this->m_obj_db->format_sql_string( $str_student_id ) . "') ";
				
					if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
					{
						$bln_success = false;
						break;
					}
					
					$str_sql_query = "SELECT P.plant_id, Problem.number_of_displayed_plants, "
								. "P.value_trait_A, P.value_trait_B, P.genotype_trait_A, "
								. "P.genotype_trait_B "
								. "FROM XG_PlantGenerates P, XG_ProblemTraitMadeFor Problem "
								. "WHERE P.generation_num = 0 "
								. "AND P.problem_id = Problem.problem_id "
								. "AND P.problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id ) . " ";
					               
					$res_plants = $this->m_obj_db->query_select( $str_sql_query ); 
					
					if ( $res_plants == null )
					{
						$str_sql_query = null;
						break;
					}

					if ( $res_plants == false )
					{
						$bln_success = false;
						break;
					}
					
					$arr_initial_plants = array();
					
					for ( $i = 0; $i < $this->m_obj_db->get_number_of_rows( $res_plants ); ++$i )
					{
						$res_row = $this->m_obj_db->fetch( $res_plants );
						array_push( $arr_initial_plants, $res_row );
					}

					$str_sql_query = "INSERT INTO XG_PlantGenerates( user_id, problem_id, generation_num, "
								. "value_trait_A, value_trait_B, genotype_trait_A, genotype_trait_B ) VALUES ";
				                    
					$int_number_of_displayed_plants = $arr_initial_plants[0]->number_of_displayed_plants;
					
					for ( $i = 0; $i < $int_number_of_displayed_plants; ++$i ) 
					{
						$obj_selected_row = $arr_initial_plants[rand( 0, count( $arr_initial_plants ) - 1 )];

						$str_sql_query = $str_sql_query . "( '"  
									. $this->m_obj_db->format_sql_string( $str_student_id ) . "', "
									. $this->m_obj_db->format_sql_string( $int_problem_id ) . ", 1, "
									. $this->m_obj_db->format_sql_string( $obj_selected_row->value_trait_A ) . ", "
									. $this->m_obj_db->format_sql_string( $obj_selected_row->value_trait_B ) . ", '"
									. $this->m_obj_db->format_sql_string( $obj_selected_row->genotype_trait_A ) . "', '"
									. $this->m_obj_db->format_sql_string( $obj_selected_row->genotype_trait_B ) . "') ";
				                       
						if ( $i != $int_number_of_displayed_plants - 1 )
						{
							$str_sql_query = $str_sql_query . ", ";
						}
					}
					
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

					(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to generate the first generation for " 
														. $this->m_obj_db->format_sql_string( $str_student_id ) . ", problem " 
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
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to assign student " 
												   . $this->m_obj_db->format_sql_string( $str_student_id ) . " to the problem " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
			return false;
		}

		if ( !$bln_success )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to generate the first generation for " 
												   . $this->m_obj_db->format_sql_string( $str_student_id ) . ", problem " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
			return false;
		}

		(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " generated the first generation for " 
											   . $this->m_obj_db->format_sql_string( $str_student_id ) . ", problem " 
											   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
	  return true;
	}
	
	function assign_student_to_course( $str_student_id, $int_course_id )
	{
		$str_sql_query = null;
		$res_check_if_exists = null;
		$str_this_user = $this->m_obj_user->str_username;
		$bln_success = true;
		
		$bln_success = true;

		// assign the problem	  
		switch ( $this->m_obj_user->int_privilege )
		{	
			case UP_PROFESSOR:
			case UP_ADMINISTRATOR:
			
				$obj_course_manager = new CourseManager( $this->m_obj_user, $this->m_obj_db );
				$int_number_of_rows = $this->m_obj_db->get_number_of_rows( $obj_course_manager->view_course_details( $int_course_id ) );
				
				if ( $int_number_of_rows != 0 )
				{
					$str_sql_query = "SELECT P.problem_id "
				                   . "FROM XG_ProblemTraitMadeFor P "
				                   . "WHERE P.course_id = " . $this->m_obj_db->format_sql_string( $int_course_id );
				                
					$res_problem_id = $this->m_obj_db->query_select( $str_sql_query ); 
					for ( $i = 0; $i < $this->m_obj_db->get_number_of_rows( $res_problem_id ); ++$i )
					{
						$res_row = $this->m_obj_db->fetch( $res_problem_id );
						$bln_success = $bln_success && $this->assign_student_to_problem( $str_student_id, $res_row->problem_id );
					}
					
				}
				
				break;
			
		}
	
	  return $bln_success;
	}
	
	function unassign_student_from_problem( $str_student_id, $int_problem_id ) 
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

				$obj_problem_manager = new ProblemManager( $this->m_obj_user, $this->m_obj_db );
				$res_check_if_exists = $obj_problem_manager->view_problem_details( $int_problem_id );

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
					$str_sql_query = "DELETE FROM XG_Assigns "
								. "WHERE problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id ) . " "
								. "AND student_id = '" . $this->m_obj_db->format_sql_string( $str_student_id ) . "'";
					
					if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
					{
						$bln_success = false;
						break;
					}
									
					$str_sql_query = "DELETE FROM XG_PlantGenerates "
								. "WHERE problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id ) . " "
								. "AND user_id = '" . $this->m_obj_db->format_sql_string( $str_student_id ) . "'";
								
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
					
					(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to delete the plants of " 
														   . $this->m_obj_db->format_sql_string( $str_student_id ) . ", problem " 
														   . $this->m_obj_db->format_sql_string( $int_problem_id ) 
														   . ", either the user does not have permission for this operation or the problem does not exist" );
														   
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
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to unassign student " 
												   . $this->m_obj_db->format_sql_string( $str_student_id ) . ", problem " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) 
												   . " due to database error" );
		  return false;
		}
		
		if ( !$bln_success )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to delete the plants of " 
												   . $this->m_obj_db->format_sql_string( $str_student_id ) . ", problem " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) 
												   . " due to database error" );
			return false;
		}

		(new Log) -> write_log_with_ip( LOG_TRANSACTION, $str_this_user . " unassigned student " 
											   . $this->m_obj_db->format_sql_string( $str_student_id ) . ", problem " 
											   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
		return true;
	}
}

?>