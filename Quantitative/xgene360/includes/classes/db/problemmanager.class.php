<?php

class ProblemManager
{
	var $m_obj_user = null;
	var $m_obj_db = null;

/**
 * Class Constructor
 * PRE: $obj_user is a user object from the cookie; 
 *      $obj_db is a DBManager object
 * POST: a ProblemManager object has been created with the parameters
 * @param $obj_user, $obj_db
 */
	function ProblemManager( $obj_user, $obj_db )
	{
	    $this->m_obj_user = $obj_user;
		$this->m_obj_db = $obj_db;
	}

/**  Function: resource view_problems
*    ---------------------------------------------------------------- 
*    Purpose:           view a list of problems 
*    Arguments:         none
*                       
*    Returns/Assigns:   resource or null or boolean
*/
	function view_problems() 
	{
		$str_sql_query = null;
		$str_this_user = $this->m_obj_user->str_username;
	  
		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_STUDENT:
			
				$str_sql_query  = "SELECT P.problem_name, P.problem_id, "
								. "UNIX_TIMESTAMP(P.start_date) AS start_date, "
								. "UNIX_TIMESTAMP(P.due_date) AS due_date, "
								. "C.Name, Gen.iter_val, P.max_cross, P.trait_A_histogram_range, P.trait_B_histogram_range "
								. "FROM XG_ProblemTraitMadeFor P, XG_Assigns A, User U, "
								. "Course C, (SELECT MAX( G.generation_num ) AS iter_val, "
								. "G.user_id, G.problem_id "
								. "FROM XG_PlantGenerates G "
								. "GROUP BY G.problem_id) AS Gen "
								. "WHERE U.UserId = A.student_id "
								. "AND A.problem_id = P.problem_id "
								. "AND P.course_id = C.CourseId "
								. "AND Gen.problem_id = P.problem_id "
								. "AND U.PrivilegeLvl = " . UP_STUDENT . " "
								. "AND U.UserId = '" . $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "'";
				break;
			   
			case UP_TA:
			   
				$str_sql_query = "SELECT P.problem_name, P.problem_id, C.Name, "
								. "UNIX_TIMESTAMP( P.start_date ) AS start_date, "
								. "UNIX_TIMESTAMP( P.due_date ) AS due_date, "
							    . "S.student_count, Sub.submit_count "
							    . "FROM Course C, User U, XG_ProblemTraitMadeFor P, XG_Assists A, "
							    . "( SELECT COUNT( A.student_id ) AS student_count, P.problem_id "
							    . "FROM XG_ProblemTraitMadeFor P LEFT OUTER JOIN XG_Assigns A "
							    . "ON P.problem_id = A.problem_id "
							    . "GROUP BY P.problem_id ) AS S, "
							    . "( SELECT COUNT( Sub.student_id ) AS submit_count, P.problem_id "
							    . "FROM XG_ProblemTraitMadeFor P LEFT OUTER JOIN XG_ProblemSolution Sub "
							    . "ON P.problem_id = Sub.problem_id "
							    . "GROUP BY P.problem_id ) AS Sub "
							    . "WHERE C.CourseId = P.course_id "
							    . "AND A.course_id = C.CourseId "
							    . "AND A.ta_id = U.UserId "
							    . "AND U.PrivilegeLvl = " . UP_TA . " "
							    . "AND U.UserId = '" . $this->m_obj_db->format_sql_string(  $this->m_obj_user->str_username ) . "' "
							    . "AND P.problem_id = S.problem_id "
							    . "AND P.problem_id = Sub.problem_id "
							    . "ORDER BY P.problem_name, C.Name ";
				break;
			   
			case UP_PROFESSOR:
			
			    $str_sql_query = "SELECT P.problem_name, P.problem_id, C.Name, "
								. "UNIX_TIMESTAMP( P.start_date ) AS start_date, "
								. "UNIX_TIMESTAMP( P.due_date ) AS due_date, "
							    . "S.student_count, Sub.submit_count "
							    . "FROM Course C, User U, XG_ProblemTraitMadeFor P, XG_Teaches T, "
							    . "( SELECT COUNT( A.student_id ) AS student_count, P.problem_id "
							    . "FROM XG_ProblemTraitMadeFor P LEFT OUTER JOIN XG_Assigns A "
							    . "ON P.problem_id = A.problem_id "
							    . "GROUP BY P.problem_id ) AS S, "
							    . "( SELECT COUNT( Sub.student_id ) AS submit_count, P.problem_id "
							    . "FROM XG_ProblemTraitMadeFor P LEFT OUTER JOIN XG_ProblemSolution Sub "
							    . "ON P.problem_id = Sub.problem_id "
							    . "GROUP BY P.problem_id ) AS Sub "
							    . "WHERE C.CourseId = P.course_id "
							    . "AND T.course_id = C.CourseId "
							    . "AND T.professor_id = U.UserId "
							    . "AND U.PrivilegeLvl = " . UP_PROFESSOR . " "
							    . "AND U.UserId = '" . $this->m_obj_db->format_sql_string(  $this->m_obj_user->str_username ) . "' "
							    . "AND P.problem_id = S.problem_id "
							    . "AND P.problem_id = Sub.problem_id "
							    . "ORDER BY P.problem_name, C.Name ";
										
				break;
							
			case UP_ADMINISTRATOR:

				$str_sql_query = "SELECT P.problem_id, P.problem_name, C.Name, "
							   . "UNIX_TIMESTAMP( P.start_date ) AS start_date, "
							   . "UNIX_TIMESTAMP( P.due_date ) AS due_date, "
							   . "S.student_count, Sub.submit_count "
							   . "FROM Course C, XG_ProblemTraitMadeFor P, "
							   . "( SELECT COUNT( A.student_id ) AS student_count, P.problem_id "
							   . "FROM XG_ProblemTraitMadeFor P LEFT OUTER JOIN XG_Assigns A "
							   . "ON P.problem_id = A.problem_id "
							   . "GROUP BY P.problem_id ) AS S, "
							   . "( SELECT COUNT( Sub.student_id ) AS submit_count, P.problem_id "
							   . "FROM XG_ProblemTraitMadeFor P LEFT OUTER JOIN XG_ProblemSolution Sub "
							   . "ON P.problem_id = Sub.problem_id "
							   . "GROUP BY P.problem_id ) AS Sub "
							   . "WHERE C.CourseId = P.course_id "
							   . "AND P.problem_id = S.problem_id "
							   . "AND P.problem_id = Sub.problem_id "
							   . "ORDER BY P.problem_name, C.Name ";
			   break;
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{ 
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to view a list of problems " );
			return null;
		}

		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " viewed a list of problems " );
		return $this->m_obj_db->query_select( $str_sql_query );
	}

/**  Function: array of problem names problem names list
*    ---------------------------------------------------------------- 
*    Purpose:           translates the problem id to problem names for the front end 
*    Arguments:         an array of problem ids
*                       
*    Returns/Assigns:   an array of arrays of (problem id, problem name)
*/
	function problem_names_list( $arr_problem_id )
	{
		$arr_problem_ids_names = array();
		
		for( $i = 0; $i < count( $arr_problem_id ); $i++ )
		{
			$res_course = $this->view_problem_details( $arr_problem_id[$i]);
			$res_row = $this->m_obj_db->fetch( $res_course );

			if ( $res_row != null && $res_row != false)
			{
				$arr_problem_id_name = array();
				array_push( $arr_problem_id_name, $res_row->problem_id );
				array_push( $arr_problem_id_name, $res_row->problem_name ) ;

				array_push( $arr_problem_ids_names, $arr_problem_id_name );
			}
		}
		
		return $arr_problem_ids_names; 	
	}

/**  Function: resource view_problem_details
*    ---------------------------------------------------------------- 
*    Purpose:           view the details of a problem
*    Arguments:         problem id
*                       
*    Returns/Assigns:   resource or boolean or null
*/
	function view_problem_details( $int_problem_id )
	{
		$str_this_user = $this->m_obj_user->str_username;
		$str_sql_query = null;
	  
		switch ( $this->m_obj_user->int_privilege )
		{
			case UP_STUDENT:
			
				$str_sql_query  = "SELECT P.problem_id, P.problem_name, P.problem_desc, P.trait_A_name, P.trait_B_name, "
								. "P.trait_A_unit, P.trait_B_unit, P.max_cross, P.number_of_displayed_plants, "
								. "P.trait_A_number_of_genes, P.trait_B_number_of_genes, P.trait_A_var, "
								. "P.trait_B_var, P.trait_A_parent_A_mean, P.trait_A_parent_B_mean, "
								. "P.trait_B_parent_A_mean, P.trait_B_parent_B_mean, P.trait_A_histogram_range, P.trait_B_histogram_range, "
								. "UNIX_TIMESTAMP(P.start_date) AS start_date, UNIX_TIMESTAMP(P.due_date) AS due_date "
								. "FROM XG_ProblemTraitMadeFor P, XG_Assigns A, User U "
								. "WHERE P.problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id ) . " "
								. "AND P.problem_id = A.problem_id "
								. "AND A.student_id = U.UserId "
								. "AND U.PrivilegeLvl = " . UP_STUDENT . " "
								. "AND U.UserId = '" . $this->m_obj_db->format_sql_string(  $this->m_obj_user->str_username ) . "'";

				break;
			   
			case UP_TA:
			   
				$str_sql_query = "SELECT P.problem_id, P.problem_name, P.problem_desc, U.UserId, U.FirstName, " 
			                   . "U.LastName, C.CourseId, C.Name, P.trait_A_name, P.trait_B_name, "
			                   . "P.trait_A_unit, P.trait_B_unit, P.trait_A_number_of_genes, "
			                   . "P.trait_B_number_of_genes, P.trait_A_h2, P.trait_B_h2, P.trait_A_var, "
			                   . "P.trait_B_var, P.trait_A_parent_A_mean, P.trait_A_parent_B_mean, "
			                   . "P.trait_B_parent_A_mean, P.trait_B_parent_B_mean, P.number_of_progeny_per_cross, "
			                   . "P.max_cross, P.number_of_displayed_plants, P.range_of_acceptance, P.trait_A_histogram_range, P.trait_B_histogram_range, "
			                   . "UNIX_TIMESTAMP(P.start_date) AS start_date, UNIX_TIMESTAMP(P.due_date) AS due_date "
			                   . "FROM XG_ProblemTraitMadeFor P, Course C, User Current, XG_Assists A, User U "
			                   . "WHERE A.ta_id = Current.UserId "
			                   . "AND Current.PrivilegeLvl = " . UP_TA . " "
			                   . "AND Current.UserId = '" . $this->m_obj_db->format_sql_string(  $this->m_obj_user->str_username ) . "' "
			                   . "AND U.UserId = P.professor_id "
			                   . "AND C.CourseId = P.course_id "
			                   . "AND C.CourseId = A.course_id "
			                   . "AND P.problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id );
			   break;
			   
			case UP_PROFESSOR:
			
				$str_sql_query = "SELECT P.problem_id, P.problem_name, P.problem_desc, U.UserId, U.FirstName, " 
			                   . "U.LastName, C.CourseId, C.Name, P.trait_A_name, P.trait_B_name, "
			                   . "P.trait_A_unit, P.trait_B_unit, P.trait_A_number_of_genes, "
			                   . "P.trait_B_number_of_genes, P.trait_A_h2, P.trait_B_h2, P.trait_A_var, "
			                   . "P.trait_B_var, P.trait_A_parent_A_mean, P.trait_A_parent_B_mean, "
			                   . "P.trait_B_parent_A_mean, P.trait_B_parent_B_mean, P.number_of_progeny_per_cross, "
			                   . "P.max_cross, P.number_of_displayed_plants, P.range_of_acceptance, P.trait_A_histogram_range, P.trait_B_histogram_range, "
			                   . "UNIX_TIMESTAMP(P.start_date) AS start_date, UNIX_TIMESTAMP( P.due_date ) AS due_date "
			                   . "FROM XG_ProblemTraitMadeFor P, Course C, User Current, XG_Teaches T, User U "
			                   . "WHERE T.professor_id = Current.UserId "
			                   . "AND Current.PrivilegeLvl = " . UP_PROFESSOR . " "
			                   . "AND Current.UserId = '" . $this->m_obj_db->format_sql_string(  $this->m_obj_user->str_username ) . "' "
			                   . "AND U.UserId = P.professor_id "
			                   . "AND C.CourseId = P.course_id "
			                   . "AND C.CourseId = T.course_id "
			                   . "AND P.problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id );

				break;
							
			case UP_ADMINISTRATOR:
			   
			    $str_sql_query = "SELECT P.problem_id, P.problem_name, P.problem_desc, U.UserId, U.FirstName, " 
			                   . "U.LastName, C.CourseId, C.Name, P.trait_A_name, P.trait_B_name, "
			                   . "P.trait_A_unit, P.trait_B_unit, P.trait_A_number_of_genes, "
			                   . "P.trait_B_number_of_genes, P.trait_A_h2, P.trait_B_h2, P.trait_A_var, "
			                   . "P.trait_B_var, P.trait_A_parent_A_mean, P.trait_A_parent_B_mean, "
			                   . "P.trait_B_parent_A_mean, P.trait_B_parent_B_mean, P.number_of_progeny_per_cross, "
			                   . "P.max_cross, P.number_of_displayed_plants, P.range_of_acceptance, P.trait_A_histogram_range, P.trait_B_histogram_range, "
			                   . "UNIX_TIMESTAMP( P.start_date ) AS start_date, UNIX_TIMESTAMP( P.due_date ) AS due_date "
			                   . "FROM XG_ProblemTraitMadeFor P, Course C, User U "
			                   . "WHERE P.professor_id = U.UserId "
			                   . "AND C.CourseId = P.course_id "
			                   . "AND P.problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id );

				break;
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to view the details of problem " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
			return null;
		}

		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " viewed the details of problem " 
											   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
		return $this->m_obj_db->query_select( $str_sql_query );
	}
	

	
	
/**  Function: resource add problem
*    ---------------------------------------------------------------- 
*    Purpose:           create problem
*    Arguments:         problem name, problem desc, course id, trait a name, trait b name, trait a unit, trait b unit,
*                       
*                       
*    Returns/Assigns:   resource or boolean or null
*/
	function add_problem( $str_problem_name, $str_problem_desc, $int_course_id, $str_trait_A_name, 
	                      $str_trait_B_name, $str_trait_A_unit, $str_trait_B_unit, 
	                      $int_trait_A_number_of_genes, $int_trait_B_number_of_genes, 
	                      $dbl_trait_A_var, $dbl_trait_B_var, $dbl_trait_A_parent_A_mean,
	                      $dbl_trait_A_parent_B_mean, $dbl_trait_B_parent_A_mean, $dbl_trait_B_parent_B_mean,
	                      $int_number_of_progeny_per_cross, $int_max_cross, $int_number_of_displayed_plants,
	                      $dbl_range_of_acceptance, $dbl_trait_A_histogram_range, $dbl_trait_B_histogram_range, $dat_start_date, $dat_due_date )
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
			
				$str_sql_query = "INSERT INTO XG_ProblemTraitMadeFor ( problem_name, problem_desc, professor_id, " 
							   . "course_id, trait_A_name, trait_B_name, trait_A_unit, trait_B_unit, "
							   . "trait_A_number_of_genes, trait_B_number_of_genes, "
							   . "trait_A_var, trait_B_var, trait_A_parent_A_mean, trait_A_parent_B_mean, "
							   . "trait_B_parent_A_mean, trait_B_parent_B_mean, number_of_progeny_per_cross, "
							   . "max_cross, number_of_displayed_plants, range_of_acceptance, trait_A_histogram_range, trait_B_histogram_range, start_date, due_date ) "
							   . "VALUES ('" . $this->m_obj_db->format_sql_string( $str_problem_name ) . "', '"
							   . $this->m_obj_db->format_sql_string( $str_problem_desc ) . "', '"
							   . $this->m_obj_db->format_sql_string( $this->m_obj_user->str_username ) . "', "
							   . $this->m_obj_db->format_sql_string( $int_course_id ) . ", '"
							   . $this->m_obj_db->format_sql_string( $str_trait_A_name ) . "', '"
							   . $this->m_obj_db->format_sql_string( $str_trait_B_name ) . "', '"
							   . $this->m_obj_db->format_sql_string( $str_trait_A_unit ) . "', '"
							   . $this->m_obj_db->format_sql_string( $str_trait_B_unit ) . "', "
							   . $this->m_obj_db->format_sql_string( $int_trait_A_number_of_genes ) . ", "
							   . $this->m_obj_db->format_sql_string( $int_trait_B_number_of_genes ) . ", "
							   . $this->m_obj_db->format_sql_string( $dbl_trait_A_var ) . ", "
							   . $this->m_obj_db->format_sql_string( $dbl_trait_B_var ) . ", "
							   . $this->m_obj_db->format_sql_string( $dbl_trait_A_parent_A_mean ) . ", "
							   . $this->m_obj_db->format_sql_string( $dbl_trait_A_parent_B_mean ) . ", "
							   . $this->m_obj_db->format_sql_string( $dbl_trait_B_parent_A_mean ) . ", "
							   . $this->m_obj_db->format_sql_string( $dbl_trait_B_parent_B_mean ) . ", "
							   . $this->m_obj_db->format_sql_string( $int_number_of_progeny_per_cross ) . ", "
							   . $this->m_obj_db->format_sql_string( $int_max_cross ) . ", "
							   . $this->m_obj_db->format_sql_string( $int_number_of_displayed_plants ) . ", "
							   . $this->m_obj_db->format_sql_string( $dbl_range_of_acceptance ) . ", "
							   . $this->m_obj_db->format_sql_string( $dbl_trait_A_histogram_range ) . ", "
							   . $this->m_obj_db->format_sql_string( $dbl_trait_B_histogram_range ) . ", '"
							   . $this->m_obj_db->format_sql_string( $dat_start_date ) . "', '"
							   . $this->m_obj_db->format_sql_string( $dat_due_date ) . "'); ";
							   
				if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
				{
					$bln_success = false;
					break;
				}

				$int_problem_id = $this->m_obj_db->get_last_inserted_id();
		
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
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to add problem " 
												   . $this->m_obj_db->format_sql_string( $str_problem_name ) );
			return false;
		}

		if ( !$bln_success )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to add problem " 
												   . $this->m_obj_db->format_sql_string( $str_problem_name ) 
												   . " due to database error" );
			return false;
		}
		

		
		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " created the problem " 
											   . $this->m_obj_db->format_sql_string( $int_problem_id ) . " " 
											   . $this->m_obj_db->format_sql_string( $str_problem_name ) );

		return $this->create_initial_plants( $int_problem_id, 
									$int_trait_A_number_of_genes, $int_trait_B_number_of_genes, 
									$dbl_trait_A_var, $dbl_trait_B_var, $dbl_trait_A_parent_A_mean,
									$dbl_trait_A_parent_B_mean, $dbl_trait_B_parent_A_mean, 
									$dbl_trait_B_parent_B_mean,	$int_number_of_progeny_per_cross );
	}

	function create_trait( $str_name, $int_number_of_genes, $dbl_parent_A_mean, $dbl_parent_B_mean, $dbl_variance_value )
	{
		// create the trait
		$obj_trait = new Traits;
		$obj_trait->create( $str_name, $int_number_of_genes, min( $dbl_parent_A_mean, $dbl_parent_B_mean ), max( $dbl_parent_A_mean, $dbl_parent_B_mean ), $dbl_variance_value );
		
		return $obj_trait;
	}

	function create_initial_plants( $int_problem_id, 
									$int_trait_A_number_of_genes, $int_trait_B_number_of_genes, 
									$dbl_trait_A_var, $dbl_trait_B_var, $dbl_trait_A_parent_A_mean,
									$dbl_trait_A_parent_B_mean, $dbl_trait_B_parent_A_mean, 
									$dbl_trait_B_parent_B_mean,	$int_number_of_progeny_per_cross )
	{

		$str_sql_query = null;
		$res_check_if_exists = null;
		$str_this_user = $this->m_obj_user->str_username;
		$bln_success = true;

		// create the initial plants
		$obj_trait_A = $this->create_trait( 'trait A', $int_trait_A_number_of_genes, $dbl_trait_A_parent_A_mean, $dbl_trait_A_parent_B_mean, $dbl_trait_A_var );
		$obj_trait_B = $this->create_trait( 'trait B', $int_trait_B_number_of_genes, $dbl_trait_B_parent_A_mean, $dbl_trait_B_parent_B_mean, $dbl_trait_B_var );

		$bln_stronger_gene_A = ( $dbl_trait_A_parent_A_mean > $dbl_trait_A_parent_B_mean );
		$bln_stronger_gene_B = ( $dbl_trait_B_parent_A_mean > $dbl_trait_B_parent_B_mean );

		$obj_plant_A = new Plant;
		$obj_plant_A->create( $int_trait_A_number_of_genes, $bln_stronger_gene_A, $int_trait_B_number_of_genes, $bln_stronger_gene_B );
		$obj_plant_B = new Plant;
		$obj_plant_B->create( $int_trait_A_number_of_genes, !$bln_stronger_gene_A, $int_trait_B_number_of_genes, !$bln_stronger_gene_B );

		// create F1 generation
		$arr_new_generation = Simulation::cross_plants( array( $obj_plant_A, $obj_plant_B ), $int_number_of_progeny_per_cross );

		// create F2 generation
		$arr_new_generation = Simulation::cross_plants( array( $arr_new_generation[0] ), $int_number_of_progeny_per_cross );
		
		$obj_generation_manager = new GenerationManager( $this->m_obj_user, $this->m_obj_db );

		if ( !$obj_generation_manager->set_array_generation( $int_problem_id, $obj_trait_A, $obj_trait_B, $arr_new_generation, 0 ) )
		{
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to create the initial plants" 
												   . " due to database error" );
			return false;
		}

		// calaculate the mean of F2
		$dbl_mean_A = 0;
		$dbl_var_A = 0;
		$dbl_mean_B = 0;
		$dbl_var_B = 0;
		
		$res_generation = $obj_generation_manager->get_initial_generation( $int_problem_id );
		
		$int_number_of_plants = $this->m_obj_db->get_number_of_rows( $res_generation );
		
		$arr_trait_A = array();
		$arr_trait_B = array();
		
		for ( $i = 0; $i < $int_number_of_plants; ++$i )
		{
			$res_row = $this->m_obj_db->fetch( $res_generation );
			
			$dbl_mean_A += $res_row->value_trait_A;
			$dbl_mean_B += $res_row->value_trait_B;
			
			array_push( $arr_trait_A, $res_row->value_trait_A );
			array_push( $arr_trait_B, $res_row->value_trait_B );
		}
		
		$dbl_mean_A /= $int_number_of_plants;
		$dbl_mean_B /= $int_number_of_plants;
		
		for ( $i = 0; $i < $int_number_of_plants; ++$i )
		{
			$dbl_var_A += ( $dbl_mean_A - $arr_trait_A[$i] ) * ( $dbl_mean_A - $arr_trait_A[$i] );
			$dbl_var_B += ( $dbl_mean_B - $arr_trait_B[$i] ) * ( $dbl_mean_B - $arr_trait_B[$i] );
		}
		
		$dbl_var_A /= ( $int_number_of_plants - 1 );
		$dbl_var_B /= ( $int_number_of_plants - 1 );
		
		// calculate the heritability
		$dbl_var_e_A = ( ( $dbl_trait_A_parent_A_mean + $dbl_trait_A_parent_B_mean + ( ( $dbl_trait_A_parent_A_mean + $dbl_trait_A_parent_B_mean ) / 2 ) ) / 3 ) * ( $dbl_trait_A_var / 100 );
		$dbl_var_e_B = ( ( $dbl_trait_B_parent_A_mean + $dbl_trait_B_parent_B_mean + ( ( $dbl_trait_B_parent_A_mean + $dbl_trait_B_parent_B_mean ) / 2 ) ) / 3 ) * ( $dbl_trait_B_var / 100 );

		$dbl_var_a_A = $dbl_var_A - $dbl_var_e_A;
		$dbl_var_a_B = $dbl_var_B - $dbl_var_e_B;
		
		$dbl_trait_A_h2 = $dbl_var_a_A / ( $dbl_var_e_A + $dbl_var_a_A );
		$dbl_trait_B_h2 = $dbl_var_a_B / ( $dbl_var_e_B + $dbl_var_a_B );


		$bln_success = true;


		$str_sql_query = "BEGIN";

		if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
		{
			$bln_success = false;
			return false;
		}

		$str_sql_query = "UPDATE XG_ProblemTraitMadeFor "
						 . "SET trait_A_h2 = " . $this->m_obj_db->format_sql_string( $dbl_trait_A_h2 ) . ", "
						 . "trait_B_h2 = " . $this->m_obj_db->format_sql_string( $dbl_trait_B_h2 ) . " "
						 . "WHERE problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id );

		if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
		{
			$bln_success = false;
			return false;
		}

		$str_sql_query = "COMMIT";

		if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
		{
			$bln_success = false;
			return false;
		}

		if( !$bln_success )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to compute heritability values" 
												   . " due to database error" );
			return false;
		}

		return true;
	}


	/* Function Description
	 * PRE: 
	 * POST: 
	 * @param 
	 */
	function delete_problem( $int_problem_id ) 
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

				$res_check_if_exists = $this->view_problem_details( $int_problem_id );

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
					$str_sql_query = "DELETE FROM XG_ProblemTraitMadeFor "
							       . "WHERE problem_id = ". $this->m_obj_db->format_sql_string( $int_problem_id );
							       
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

					Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " does not have permission to modify problem " 
														   . $this->m_obj_db->format_sql_string( $int_problem_id ) 
														   . " or problem does not exist" );
					return false;
				}
			}
			break;
			
			// continue to delete problem if the problem DOES belong to that prof
		}

		if ( $str_this_user == null )
		{
			$str_this_user = "<user did not login>";
		}

		if ( $str_sql_query == null )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to delete " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
			return false;
		}
		
		if ( !$bln_success )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to delete " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) 
												   . " due to database error." );
			return false;
		}
		
		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " deleted " 
											   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
		return true;
		
	}



	function modify_problem( $int_problem_id, $str_problem_name, $str_problem_description,
							 $str_trait_A_name, $str_trait_B_name, $str_trait_A_unit, 
	                         $str_trait_B_unit, $int_trait_A_number_of_genes, $int_trait_B_number_of_genes, 
	                         $dbl_trait_A_var, $dbl_trait_B_var, $dbl_trait_A_parent_A_mean, 
	                         $dbl_trait_A_parent_B_mean, $dbl_trait_B_parent_A_mean, $dbl_trait_B_parent_B_mean,
	                         $int_number_of_progeny_per_cross, $int_max_cross, $int_number_of_displayed_plants,
	                         $dbl_range_of_acceptance, $dbl_trait_A_histogram_range, $dbl_trait_B_histogram_range, $dat_start_date, $dat_due_date )
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

				$res_check_if_exists = $this->view_problem_details( $int_problem_id );

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
					$bln_changed = true;

					$res_problem2 = $this->view_problem_details( $int_problem_id );
					$res_details = $this->m_obj_db->fetch( $res_problem2 );
					
					if ( ( $int_trait_A_number_of_genes == $res_details->trait_A_number_of_genes ) &&
						( $int_trait_B_number_of_genes == $res_details->trait_B_number_of_genes ) && 
						( $dbl_trait_A_var == $res_details->trait_A_var ) && 
						( $dbl_trait_B_var == $res_details->trait_B_var ) && 
						( $dbl_trait_A_parent_A_mean == $res_details->trait_A_parent_A_mean ) && 
						( $dbl_trait_A_parent_B_mean == $res_details->trait_A_parent_B_mean ) && 
						( $dbl_trait_B_parent_A_mean == $res_details->trait_B_parent_A_mean ) && 
						( $dbl_trait_B_parent_B_mean == $res_details->trait_B_parent_B_mean ) && 
						( $int_number_of_progeny_per_cross == $res_details->number_of_progeny_per_cross ) )
					{
						$bln_changed = false;
					}

					$str_sql_query = "UPDATE XG_ProblemTraitMadeFor " 
								. "SET problem_name = '" . $this->m_obj_db->format_sql_string( $str_problem_name ) . "', "
								. "problem_desc = '" . $this->m_obj_db->format_sql_string( $str_problem_description ) . "', "
								. "trait_A_name = '" . $this->m_obj_db->format_sql_string( $str_trait_A_name ) . "', "
								. "trait_B_name = '" . $this->m_obj_db->format_sql_string( $str_trait_B_name ) . "', "
								. "trait_A_unit = '" . $this->m_obj_db->format_sql_string( $str_trait_A_unit ) . "', "
								. "trait_B_unit = '" . $this->m_obj_db->format_sql_string( $str_trait_B_unit ) . "', "
								. "trait_A_number_of_genes = " . $this->m_obj_db->format_sql_string( $int_trait_A_number_of_genes ) . ", "
								. "trait_B_number_of_genes = " . $this->m_obj_db->format_sql_string( $int_trait_B_number_of_genes ) . ", "
								. "trait_A_var = " . $this->m_obj_db->format_sql_string( $dbl_trait_A_var ) . ", "
								. "trait_B_var = " . $this->m_obj_db->format_sql_string( $dbl_trait_B_var ) . ", "
								. "trait_A_parent_A_mean = " . $this->m_obj_db->format_sql_string( $dbl_trait_A_parent_A_mean ) . ", "
								. "trait_A_parent_B_mean = " . $this->m_obj_db->format_sql_string( $dbl_trait_A_parent_B_mean ) . ", "
								. "trait_B_parent_A_mean = " . $this->m_obj_db->format_sql_string( $dbl_trait_B_parent_A_mean ) . ", "
								. "trait_B_parent_B_mean = " . $this->m_obj_db->format_sql_string( $dbl_trait_B_parent_B_mean ) . ", "
								. "number_of_progeny_per_cross = " . $this->m_obj_db->format_sql_string( $int_number_of_progeny_per_cross ) . ", "
								. "max_cross = " . $this->m_obj_db->format_sql_string( $int_max_cross ) . ", "
								. "number_of_displayed_plants = " . $this->m_obj_db->format_sql_string( $int_number_of_displayed_plants ) . ", "
								. "range_of_acceptance = " . $this->m_obj_db->format_sql_string( $dbl_range_of_acceptance ) . ", "
								. "trait_A_histogram_range = " . $this->m_obj_db->format_sql_string( $dbl_trait_A_histogram_range ) . ", "
								. "trait_B_histogram_range = " . $this->m_obj_db->format_sql_string( $dbl_trait_B_histogram_range ) . ", "
								. "start_date = '" . $this->m_obj_db->format_sql_string( $dat_start_date ) . "', "
								. "due_date = '" . $this->m_obj_db->format_sql_string( $dat_due_date ) . "' "
								. "WHERE problem_id = " . $this->m_obj_db->format_sql_string(  $int_problem_id );
							   
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
				if ( $bln_changed )
				{

					$obj_assign_problem_manager = new AssignProblemManager( $this->m_obj_user, $this->m_obj_db );
					$res_student_ids2 = $obj_assign_problem_manager->view_students_assigned_to_problem( $int_problem_id ); 
					$arr_student_id = array();
					
					for ( $i = 0; $i < $this->m_obj_db->get_number_of_rows( $res_student_ids2 ); $i++ )
					{
						$res_row3 = $this->m_obj_db->fetch( $res_student_ids2 );
						
						$str_student_ids2 = $res_row3->UserId;
						
						array_push( $arr_student_id, $str_student_ids2);
					}
					
					$str_sql_query = "DELETE FROM XG_PlantGenerates WHERE problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id );
					
					if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
					{
						$bln_success = false;
						break;
					}

					$this->create_initial_plants( $int_problem_id, 
									$int_trait_A_number_of_genes, $int_trait_B_number_of_genes, 
									$dbl_trait_A_var, $dbl_trait_B_var, $dbl_trait_A_parent_A_mean,
									$dbl_trait_A_parent_B_mean, $dbl_trait_B_parent_A_mean, 
									$dbl_trait_B_parent_B_mean,	$int_number_of_progeny_per_cross );
					
					for ( $j = 0; $j < count( $arr_student_id ); $j++ )
					{
						$str_student_id = $arr_student_id[$j];
						
						$str_sql_query = "SELECT P.plant_id, Problem.number_of_displayed_plants, "
									. "P.value_trait_A, P.value_trait_B, P.genotype_trait_A, "
									. "P.genotype_trait_B "
									. "FROM XG_PlantGenerates P, XG_ProblemTraitMadeFor Problem "
									. "WHERE P.generation_num = 0 "
									. "AND P.problem_id = Problem.problem_id "
									. "AND P.problem_id = " . $this->m_obj_db->format_sql_string( $int_problem_id ) . " ";
						               
						$res_plants = $this->m_obj_db->query_select( $str_sql_query ); 
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
					}
					
					$str_sql_query = "COMMIT";

					if ( !$this->m_obj_db->query_commit( $str_sql_query ) )
					{
						$bln_success = false;
						break;
					}
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

					Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " does not have permission to modify problem  " 
														   . $this->m_obj_db->format_sql_string( $int_problem_id ) 
														   . " or problem does not exist");

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
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " attempted to modify " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
			return null;
		}
		
		if ( !$bln_success )
		{
			$this->m_obj_db->query_commit( "ROLLBACK" );
			Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " failed to modify problem " 
												   . $this->m_obj_db->format_sql_string( $int_problem_id ) 
												   . " due to database error");
			return false;
		}

		Log::write_log_with_ip( LOG_TRANSACTION, $str_this_user . " modified problem " 
											   . $this->m_obj_db->format_sql_string( $int_problem_id ) );
		return true;
	}
}

?>