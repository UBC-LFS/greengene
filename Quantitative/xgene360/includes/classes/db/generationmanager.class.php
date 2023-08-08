<?php

class GenerationManager
{
	var $m_obj_user = null;
	var $m_obj_db = null;

/**
 * Class Constructor
 * PRE: $obj_user is a user object from the cookie; 
 *      $obj_db is a DBManager object
 * POST: a GenerationManager object has been created with the parameters
 * @param $obj_user, $obj_db
 */
	// function GenerationManager( $obj_user, $obj_db )
	function __construct( $obj_user, $obj_db ) 
	{
	    $this->m_obj_user = $obj_user;
		$this->m_obj_db = $obj_db;
	}
	
/**  Function: resource get_number_of_generations
*    ---------------------------------------------------------------- 
*    Purpose:           get the number of generations for a student with that particular problem
*    Arguments:         student id, problem id
*                       
*    Returns/Assigns:   resource or boolean or null
*/
	function get_number_of_generations( $str_student_id, $int_problem_id )
	{
		$str_sql_query = null;
		$str_this_user = $this->m_obj_user->str_username;
		
		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_ADMINISTRATOR:
			case UP_PROFESSOR:
			case UP_TA:
			
				$str_sql_query  = "SELECT MAX( P.generation_num ) AS generation_count "
								. "FROM XG_PlantGenerates P "
								. "WHERE P.user_id = '" . $this->m_obj_db->format_sql_string( $str_student_id ) . "' "
								. "AND P.problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id ); 
				break;
			
			case UP_STUDENT:
			
				$str_sql_query  = "SELECT MAX( P.generation_num ) AS generation_count "
								. "FROM XG_PlantGenerates P "
								. "WHERE P.user_id = '" . $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "' "
								. "AND P.problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id ); 
				break;
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to retrieve the number of generations for problem " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
			return null;
		}

		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " retrieved the number of generations for problem " 
											   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
		return $this->m_obj_db->query_select( $str_sql_query );
	}
	

/**  Function: resource get_initial generation
*    ---------------------------------------------------------------- 
*    Purpose:           get the initial generation of a problem
*    Arguments:         problem id
*                       
*    Returns/Assigns:   resource or boolean or null
*/
	 function get_initial_generation( $int_problem_id )
	{
		$str_sql_query = null;
		$str_this_user = $this->m_obj_user->str_username;

		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_PROFESSOR:
			case UP_ADMINISTRATOR:
			
				$str_sql_query  = "SELECT P.plant_id, P.generation_num, P.value_trait_A, "
			                   . "P.value_trait_B "
			                   . "FROM XG_PlantGenerates P " 
			                   . "WHERE P.user_id = '" . $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "' "
			                   . "AND P.problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id ) . " "
			                   . "AND P.generation_num = " . 0 . " "
			                   . "ORDER BY P.generation_num, P.plant_id";
				break;
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to retrieve the plants for problem  " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) . ", initial generation " );
			return null;
		}

		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " retrieved the plants for problem  " 
											   . $this->m_obj_db->format_sql_string( $int_problem_id ) . ", initial generation " );	  
		return $this->m_obj_db->query_select( $str_sql_query );
	}

	 
	 

	 // function that gets all the previous stuffie
/**  Function: resource get generation
*    ---------------------------------------------------------------- 
*    Purpose:           gets the gerneration for a particular student, generation and problem
*    Arguments:         problem id, student id, generation id
*                       
*    Returns/Assigns:   resource or boolean or null
*/
	function get_generation( $str_student_id, $int_problem_id, $int_generation_id ) 
	{
		$str_sql_query = null;
		$str_this_user = $this->m_obj_user->str_username;

		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_STUDENT:
			
				$str_sql_query  = "SELECT P.plant_id, P.generation_num, P.value_trait_A, "
			                   . "P.value_trait_B "
			                   . "FROM XG_PlantGenerates P " 
			                   . "WHERE P.user_id = '" . $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "' "
			                   . "AND P.problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id ) . " "
			                   . "AND P.generation_num = " . $this->m_obj_db->format_sql_string( $int_generation_id ) . " "
			                   . "ORDER BY P.generation_num, P.plant_id";
				break;

			
			case UP_TA:
			case UP_PROFESSOR:
			case UP_ADMINISTRATOR:
			
			   $str_sql_query  = "SELECT P.plant_id, P.generation_num, P.value_trait_A, "
			                   . "P.value_trait_B "
			                   . "FROM XG_PlantGenerates P " 
			                   . "WHERE P.user_id = '" . $this->m_obj_db->format_sql_string( $str_student_id ) . "' "
			                   . "AND P.problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id ) . " "
			                   . "AND P.generation_num = " . $this->m_obj_db->format_sql_string( $int_generation_id ) . " "
			                   . "ORDER BY P.generation_num, P.plant_id";
			   break;

			
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to retrieve the plants for problem  " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) . ", generation "
												   . $this->m_obj_db->format_sql_string( $int_generation_id ) );
			return null;
		}

		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " retrieved the plants for problem  " 
											   . $this->m_obj_db->format_sql_string( $int_problem_id ) . ", generation "
											   . $this->m_obj_db->format_sql_string( $int_generation_id ) );	  
		return $this->m_obj_db->query_select( $str_sql_query );
	}


	 // function insert all the entire generation
/**  Function: resource set_array_generation
*    ---------------------------------------------------------------- 
*    Purpose:           gets the gerneration for a particular student, generation and problem
*    Arguments:         problem id, obj trait a, obj trait b, generation id
*                       
*    Returns/Assigns:   boolean or null
*/
	function set_array_generation( $int_problem_id, $obj_trait_A, $obj_trait_B, $arr_generation, $int_generation_num ) 
	{
		$str_sql_query = null;
		$res_check_if_exists = null;
		$str_this_user = $this->m_obj_user->str_username;
		$bln_success = true;
	  
		switch ( $this->m_obj_user->int_privilege )
		{ 
			case UP_ADMINISTRATOR:
			case UP_PROFESSOR:
			case UP_STUDENT:
			{
				$str_sql_query = "BEGIN";

				if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
				{
					$bln_success = false;
					break;
				}
				
				$str_sql_query = "INSERT INTO XG_PlantGenerates( user_id, problem_id, generation_num, "
								. "value_trait_A, value_trait_B, genotype_trait_A, genotype_trait_B ) "
								. "VALUES ";
				                      
				for( $i = 0; $i < count( $arr_generation ); $i++)
				{
					$str_sql_query = $str_sql_query . "( '" 
									. $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "', "
									. $this->m_obj_db->format_sql_string( $int_problem_id ) . ", "
									. $this->m_obj_db->format_sql_string( $int_generation_num ) . ", "
									. $this->m_obj_db->format_sql_string( $arr_generation[$i]->calculate_trait_A( $obj_trait_A ) ) . ", "
									. $this->m_obj_db->format_sql_string( $arr_generation[$i]->calculate_trait_B( $obj_trait_B ) ) . ", "
									. "'" . $this->m_obj_db->format_sql_string( $arr_generation[$i]->arr_gene[0]->encrypt() ) . "', "
									. "'" . $this->m_obj_db->format_sql_string( $arr_generation[$i]->arr_gene[1]->encrypt() ) . "' ) ";

					if ( $i != count( $arr_generation ) - 1 )
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
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempt to generate the plants for problem " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) . ", generation "
												   . $this->m_obj_db->format_sql_string( $int_generation_num ) );
			return false;
		}

		if ( !$bln_success )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to generate the plants for problem " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) . ", generation "
												   . $this->m_obj_db->format_sql_string( $int_generation_num ) 
												   . " due to database error" );
			return false;
		}
		
		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " generated the plants for problem " 
											   . $this->m_obj_db->format_sql_string( $int_problem_id ) . ", generation "
											   . $this->m_obj_db->format_sql_string( $int_generation_num ) );
		return true;
	}

/**  Function: resource get_parents_genotypes
*    ---------------------------------------------------------------- 
*    Purpose:           get teh plant genotypes
*    Arguments:         problem id, a array of plant id
*                       
*    Returns/Assigns:   array of arrays of genotypes
*/

	function get_parents_genotypes( $int_problem_id, $arr_parents )
	{
		$str_sql_query = null;
		$str_this_user = $this->m_obj_user->str_username;
		
		switch ($this->m_obj_user->int_privilege)
		{
			case UP_STUDENT:
			
			$arr_plants_genotype = array();
			
			for( $i = 0; $i < count($arr_parents); $i++ )
			{
			   $str_sql_query  = "SELECT P.genotype_trait_A, P.genotype_trait_B "
			                   . "FROM XG_PlantGenerates P " 
			                   . "WHERE P.plant_id = " . $this->m_obj_db->format_sql_string( $arr_parents[$i] ) . " "
			                   . "AND P.problem_id = ". $this->m_obj_db->format_sql_string( $int_problem_id );
			                   
			   $res_plants = $this->m_obj_db->query_select( $str_sql_query );
			   $res_row = $this->m_obj_db->fetch( $res_plants );
			   $str_genotype_trait_A = $res_row->genotype_trait_A;
			   $str_genotype_trait_B = $res_row->genotype_trait_B;
			   
			   $arr_genotype = array();
			   array_push($arr_genotype, $str_genotype_trait_A);
			   array_push($arr_genotype, $str_genotype_trait_B);
			   
			   array_push($arr_plants_genotype, $arr_genotype);
			}
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to retrieve the genotypes of plants for breeding, problem " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
			return null;
		}

		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " retrieved the genotypes of plants for breeding, problem " 
											   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
		return $arr_plants_genotype;
	}

/**  Function: resource set_parents
*    ---------------------------------------------------------------- 
*    Purpose:           inserts the parents into the parent table
*    Arguments:         problem id, array of plant ids
*                       
*    Returns/Assigns:   boolean or null
*/
	function set_parents( $int_problem_id, $arr_parents )
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

				$str_sql_query = "SELECT MAX(P.generation_num) AS gen_num "
							   . "FROM XG_PlantGenerates P "
							   . "WHERE P.problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id ) . " "
							   . "AND P.user_id = '" . $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "' ";
			                 
				$res_gen_num = $this->m_obj_db->query_select( $str_sql_query );
				
				if ( $res_gen_num == null )
				{
					$str_sql_query = null;
					break;
				}

				if ( $res_gen_num == false )
				{
					$bln_success = false;
					break;
				}
				
				$res_row = $this->m_obj_db->fetch( $res_gen_num );
				$int_generation_num = (int)$res_row->gen_num + 1;

				$str_sql_query = "INSERT INTO XG_PlantParents( plant_id, user_id, problem_id, generation_num ) "
				               . "VALUES ";
			                      
			    for( $i = 0; $i < count( $arr_parents ); $i++)
			    {
				   $str_sql_query = $str_sql_query . "( " 
				 . $this->m_obj_db->format_sql_string( $arr_parents[$i] ) . ", '"
				 . $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "', "
				 . $this->m_obj_db->format_sql_string( $int_problem_id ) . ", "
                 . $this->m_obj_db->format_sql_string( $int_generation_num ) . ") ";
                                      
				  if ( $i != count( $arr_parents ) - 1 )
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
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to select the plants for the next generation, problem " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
			return false;
		}

		if ( !$bln_success )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to select the plants for the next generation, problem " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) 
												   . " due to database error" );
			return false;
		}

		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " selected the plants for the next generation, problem " 
											   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
		return true;
	}

/**  Function: resource get_parents_trait_values
*    ---------------------------------------------------------------- 
*    Purpose:           gets parent trait values of the plants
*    Arguments:         problem id, student, generation id
*                       
*    Returns/Assigns:   resource boolean or null
*/
	function get_parents_trait_values( $str_student_id, $int_problem_id, $int_generation_id )
	{
		$str_sql_query = null;
		$str_this_user = $this->m_obj_user->str_username;
		$arr_parents_trait_values = array();
		
		switch ($this->m_obj_user->int_privilege)
		{
			case UP_TA:
			case UP_PROFESSOR:
			case UP_ADMINISTRATOR:

				$str_sql_query  = "SELECT Plant.value_trait_A, Plant.value_trait_B "
			                    . "FROM XG_PlantGenerates Plant, XG_PlantParents Parent " 
			                    . "WHERE Plant.user_id = Parent.user_id "
			                    . "AND Parent.user_id = '" . $this->m_obj_db->format_sql_string( $str_student_id ) . "' "
			                    . "AND Plant.problem_id = Parent.problem_id "
			                    . "AND Parent.problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id ) . " "
			                    . "AND Parent.generation_num = " . $this->m_obj_db->format_sql_string( $int_generation_id ) . " "
			                    . "AND Plant.plant_id = Parent.plant_id ";
			                    
				$res_plants = $this->m_obj_db->query_select( $str_sql_query );
				
				for ( $i = 0; $i < $this->m_obj_db->get_number_of_rows( $res_plants ); $i++ )
				{
					$res_row = $this->m_obj_db->fetch( $res_plants );
					$arr_trait_values = array();
					
					array_push( $arr_trait_values, $res_row->value_trait_A );
					array_push( $arr_trait_values, $res_row->value_trait_B );
					
					array_push( $arr_parents_trait_values, $arr_trait_values );
				}

				break;
			
			
			case UP_STUDENT:
			
				$str_sql_query  = "SELECT Plant.value_trait_A, Plant.value_trait_B "
			                    . "FROM XG_PlantGenerates Plant, XG_PlantParents Parent " 
			                    . "WHERE Plant.user_id = Parent.user_id "
			                    . "AND Parent.user_id = '" . $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "' "
			                    . "AND Plant.problem_id = Parent.problem_id "
			                    . "AND Parent.problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id ) . " "
			                    . "AND Parent.generation_num = " . $this->m_obj_db->format_sql_string( $int_generation_id ) . " "
			                    . "AND Plant.plant_id = Parent.plant_id ";
			                    
				$res_plants = $this->m_obj_db->query_select( $str_sql_query );
				
				for ( $i = 0; $i < $this->m_obj_db->get_number_of_rows( $res_plants ); $i++ )
				{
					$res_row = $this->m_obj_db->fetch( $res_plants );
					$arr_trait_values = array();
					
					array_push( $arr_trait_values, $res_row->value_trait_A );
					array_push( $arr_trait_values, $res_row->value_trait_B );
					
					array_push( $arr_parents_trait_values, $arr_trait_values );
				}

				break;
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to retrieve the parents' trait values for problem " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
			return null;
		}

		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " retrieved the parents' trait values for problem " 
											   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
		return $arr_parents_trait_values;
	}	
	
}

?>