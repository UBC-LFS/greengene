<?php

class Gene
{
	var $str_gene = '';
	
	/**  Function: void create( $int_number_of_genes, $bln_is_strong_gene )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Create new gene
	*    Arguments:         $int_number_of_genes - int, number of genes
	*                       $bln_is_strong_gene - bool, whether the gene is a strong gene
	*
	*    Returns/Assigns:   None
	*/
	function create( $int_number_of_genes, $bln_is_strong_gene )
	{
		if ( $bln_is_strong_gene )
		{
			$this->str_gene = str_repeat( '1', $int_number_of_genes * 2 );
		}
		
		else
		{
			$this->str_gene = str_repeat( '0', $int_number_of_genes * 2 );
		}
		
		// make sure the length of the string is a multiple of 8
		// this ensures that each gene segment after encrypted will be a byte
		if ( strlen( $this->str_gene ) % 8 != 0 )
		{
			$this->str_gene = str_pad( $this->str_gene, strlen( $this->str_gene ) + ( 8 - strlen( $this->str_gene ) % 8 ), '0', STR_PAD_LEFT );
		}
	}
	
	/**  Function: void decrypt( $str_gene )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Convert the gene in string representation to
	*                       binary representation
	*    Arguments:         $str_gene - string, the gene in string
	*                       representation
	*                       
	*    Returns/Assigns:   Assigns the gene in binary representation
	*/
	function decrypt( $str_gene )
	{
		// reinitialize to empty string
		$this->str_gene = '';
		
		$str_gene = stripslashes( $str_gene );
		
		for ( $i = 0; $i < strlen( $str_gene ); $i=$i+2 )
		{
			// make sure it's 8 bits per block
			$tmp_bin_gene = str_pad( base_convert( substr( $str_gene, $i, 2 ) , 16, 2 ), 8, '0', STR_PAD_LEFT );
			
			$this->str_gene = $this->str_gene.$tmp_bin_gene;
		}
	}
	
	/**  Function: string encrypt()
	*    ---------------------------------------------------------------- 
	*    Purpose:           Convert the gene in binary representation to
	*                       string representation
	*    Arguments:         None
	*                       
	*    Returns/Assigns:   Returns the gene in string representation
	*/
	function encrypt()
	{
		$tmp_str_result = '';
		
		// make sure the length of the string is a multiple of 8
		// this ensures that each gene segment after encrypted will be a byte
		if ( strlen( $this->str_gene ) % 8 != 0 )
		{
			$this->str_gene = str_pad( $this->str_gene, strlen( $this->str_gene ) + ( 8 - strlen( $this->str_gene ) % 8 ), '0', STR_PAD_LEFT );
		}

		// convert the string to binary representation
		$this->str_gene = addslashes( $this->str_gene );
		for ( $i = 0; $i < strlen( $this->str_gene ); $i = $i + 8 )
		{
			$data = base_convert( substr($this->str_gene, $i, 8) , 2, 16);
			$data = str_pad($data, 2, "0", STR_PAD_LEFT);
			$tmp_str_result = $tmp_str_result .  $data;
		}
		
		return $tmp_str_result;
	}
	
	/**  Function: int get_number_of_strong_genes()
	*    ---------------------------------------------------------------- 
	*    Purpose:           Get the number of strong genes
	*    Arguments:         None
	*                       
	*    Returns/Assigns:   Returns the number of string genes
	*/
	function get_number_of_strong_genes()
	{
		$tmp_int_count = 0;
		
		for ( $i = 0; $i < strlen( $this->str_gene ); ++$i )
		{
			if ( $this->str_gene[$i] == '1' )
			{
				$tmp_int_count++;
			}
		}
		
		return $tmp_int_count;
	}	
}

class Traits
{
	var $str_name;
	var $int_number_of_genes;
	var $dbl_base_value;
	var $dbl_strong_value;
	var $dbl_capital_value;
	var $dbl_variance_value;
	
	/**  Function: void create( $str_name, $int_number_of_genes, $dbl_base_value, $dbl_strong_value, $dbl_variance_value )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Create new trait
	*    Arguments:         $str_name - string, the trait name
	*                       $int_number_of_genes - int, the number of genes
	*                       $dbl_base_value - double, the base value
	*                       $dbl_strong_value - double, the strong value
	*                       $dbl_variance_value - double, the environemnt
	*                       variance
	*
	*    Returns/Assigns:   Set the apropriate values
	*/
	function create( $str_name, $int_number_of_genes, $dbl_base_value, $dbl_strong_value, $dbl_variance_value )
	{
		$this->str_name = $str_name;
		$this->int_number_of_genes = $int_number_of_genes;
		$this->dbl_base_value = $dbl_base_value;
		$this->dbl_strong_value = $dbl_strong_value;
		$this->dbl_variance_value = $dbl_variance_value;
		
		// calcuate the value for what each capital letter contributes
		$this->dbl_capital_value = ( $dbl_strong_value - $dbl_base_value ) / $int_number_of_genes / 2;
	}
}

class Plant
{
	var $arr_gene;
		
	/**  Function: void create( $int_number_of_genes1, $bln_strong_gene1, $int_number_of_genes2, $bln_strong_gene2 )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Create new trait
	*    Arguments:         $int_number_of_genes1 - int, number of genes
	*                       in the first trait
	*                       $bln_strong_gene1 - int, number of strong
	*                       genes in the first trait
	*                       int_number_of_genes2 - int, number of genes
	*                       in the second trait
	*                       $bln_strong_gene2 - int, number of strong
	*                       genes in the second trait
	*
	*    Returns/Assigns:   Set the apropriate values
	*/
	function create( $int_number_of_genes1, $bln_strong_gene1, $int_number_of_genes2, $bln_strong_gene2 )
	{
		$this->arr_gene[0] = new Gene;
		$this->arr_gene[0]->create($int_number_of_genes1, $bln_strong_gene1 );
		$this->arr_gene[1] = new Gene;
		$this->arr_gene[1]->create( $int_number_of_genes2, $bln_strong_gene2 );
	}
	
	/**  Function: calculate_trait_A( $obj_trait )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Calculate the trait A value
	*    Arguments:         $obj_trait - object, the trait
	*                       
	*    Returns/Assigns:   Returns the trait value
	*/
	function calculate_trait_A( $obj_trait )
	{
		return $this->calculate_trait( $obj_trait, $this->arr_gene[0] );
	}
	
	/**  Function: calculate_trait_B( $obj_trait )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Calculate the trait B value
	*    Arguments:         $obj_trait - object, the trait
	*                       
	*    Returns/Assigns:   Returns the trait value
	*/
	function calculate_trait_B( $obj_trait )
	{
		return $this->calculate_trait( $obj_trait, $this->arr_gene[1] );
	}
	
	/**  Function: calculate_trait( $obj_trait )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Calculate the trait value
	*    Arguments:         $obj_trait - object, the trait
	*                       
	*    Returns/Assigns:   Returns the trait value
	*/
	function calculate_trait( $obj_trait, $obj_gene )
	{
		return Simulation::generate_normal_random( $obj_trait->dbl_base_value + $obj_gene->get_number_of_strong_genes() * $obj_trait->dbl_capital_value, $obj_trait->dbl_variance_value );
	}
}
	
class Simulation
{
	/**  Function: array cross_plants( $arr_plants, $int_number_of_offspring )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Cross plants
	*    Arguments:         $arr_plants - array, the parent plants
	*                       $int_number_of_offspring - int, the number of
	*                       offspring to generate
	*                       
	*    Returns/Assigns:   Returns an array of next generation
	*/
	function cross_plants( $arr_plants, $int_number_of_offspring )
	{
		// if we are doing self-crossing, we will need two exactly same plants
		if ( count( $arr_plants ) == 1 )
		{
			$arr_plants[ 1 ] = $arr_plants[ 0 ];
		}
		
		// if we have multiple parents, then we will need to know how many plants we are going to pick
		// from each pair of the parents, so calculate
		$tmp_int_number_of_cross = count( $arr_plants ) * ( count( $arr_plants ) - 1 ) / 2;
		$tmp_int_plants_per_recombination = floor( $int_number_of_offspring / $tmp_int_number_of_cross );	// number of plants picked from each pair of parents
		$tmp_int_plants_remained = $int_number_of_offspring - $tmp_int_plants_per_recombination * $tmp_int_number_of_cross;
		
		$tmp_arr_next_generation = array();
		
		for ( $i = 0; $i < count( $arr_plants ) - 1; ++$i )
		{
			for ( $j = $i + 1; $j < count( $arr_plants ); ++$j )
			{
				if ( $i == count( $arr_plants ) - 2 && $j == count( $arr_plants ) - 1 )
				{
					$tmp_arr_next_generation = array_merge( $tmp_arr_next_generation, Simulation::combine_plants( $arr_plants[$i], $arr_plants[$j], $tmp_int_plants_per_recombination + $tmp_int_plants_remained ) );
				}
				
				else
				{
					$tmp_arr_next_generation = array_merge( $tmp_arr_next_generation, Simulation::combine_plants( $arr_plants[$i], $arr_plants[$j], $tmp_int_plants_per_recombination ) );
				}
			}
		}
		
		return $tmp_arr_next_generation;
	}
	
	/**  Function: array combine_plants( $obj_plant1, $obj_plant2, $int_number_of_offspring_to_pick )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Combine two plants to generate next
	*                       generation
	*    Arguments:         $obj_plant1 - object, the plant 1
	*                       $obj_plant2 - object, the plant 2
	*                       $int_number_of_offspring_to_pick - the number
	*                       of offspring to generate
	*                       
	*    Returns/Assigns:   Returns an array of next generation
	*/
	function combine_plants( $obj_plant1, $obj_plant2, $int_number_of_offspring_to_pick )
	{
		$tmp_arr_next_generations_gene1 = Simulation::combine_genes( $obj_plant1->arr_gene[0], $obj_plant2->arr_gene[0], $int_number_of_offspring_to_pick );
		$tmp_arr_next_generations_gene2 = Simulation::combine_genes( $obj_plant1->arr_gene[1], $obj_plant2->arr_gene[1], $int_number_of_offspring_to_pick );
		
		$tmp_arr_next_generations = array();
		
		for ( $i = 0; $i < count( $tmp_arr_next_generations_gene1 ); ++$i )
		{
			$obj_plant = new Plant;
			$obj_plant->arr_gene[0] = $tmp_arr_next_generations_gene1[$i];
			$obj_plant->arr_gene[1] = $tmp_arr_next_generations_gene2[$i];
			
			$tmp_arr_next_generations[$i] = $obj_plant;
		}
		
		return $tmp_arr_next_generations;
	}
	
	/**  Function: array combine_genes( $obj_gene1, $obj_gene2, $int_number_of_offspring_to_pick )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Create the gene combination
	*    Arguments:         $obj_gene1 - object, the first gene
	*                       $obj_gene2 - object, the second gene
	*                       $int_number_of_offspring_to_pick - the number
	*                       of offspring to generate
	*                       
	*    Returns/Assigns:   Returns an array of next generation
	*/
	function combine_genes( $obj_gene1, $obj_gene2, $int_number_of_offspring_to_pick )
	{
		$int_array_index = 0;
		
		$tmp_arr_gene_possibilities = array();
		$tmp_arr_gene_to_return = array();
		
		if ( strlen( $obj_gene1->str_gene ) == strlen( $obj_gene2->str_gene ) )
		{
			for ( $i = 0; $i < strlen( $obj_gene1->str_gene ); $i = $i + 2 )
			{
				$tmp_str_gene1 = substr( $obj_gene1->str_gene, $i, 2 );
				$tmp_str_gene2 = substr( $obj_gene2->str_gene, $i, 2 );
				
				$tmp_arr[0] = $tmp_str_gene1[0].$tmp_str_gene2[0];
				$tmp_arr[1] = $tmp_str_gene1[0].$tmp_str_gene2[1];
				$tmp_arr[2] = $tmp_str_gene1[1].$tmp_str_gene2[0];
				$tmp_arr[3] = $tmp_str_gene1[1].$tmp_str_gene2[1];
				
				$tmp_arr_gene_possibilities[$int_array_index] = $tmp_arr;
				$int_array_index++;
			}
			
			for ( $i = 0; $i < $int_number_of_offspring_to_pick; ++$i )
			{
				$obj_gene = new Gene;
				
				for ( $j = 0; $j < count( $tmp_arr_gene_possibilities ); ++$j )
				{
					$obj_gene->str_gene = $obj_gene->str_gene.$tmp_arr_gene_possibilities[$j][mt_rand( 0, 3 )];
				}
				
				$tmp_arr_gene_to_return[$i] = $obj_gene;
			}
		}
		
		return $tmp_arr_gene_to_return;
	}
	
	/**  Function: double generate_normal_random( $dbl_calculated_mean, $dbl_percentage_variance )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Generate a normally distributed number
	*    Arguments:         $dbl_calculated_mean - double, the mean
	*                       $dbl_percentage_variance - double, the variance
	*                       
	*    Returns/Assigns:   Returns randomly distribute number
	*/	
	function generate_normal_random( $dbl_calculated_mean, $dbl_percentage_variance )
	{
		$dbl_variance = $dbl_percentage_variance / 100;
		$dbl_max = $dbl_calculated_mean * ( 1 + $dbl_variance );
		$dbl_min = $dbl_calculated_mean * ( 1 - $dbl_variance );
		
		return mt_rand() / mt_getrandmax() * ( $dbl_max - $dbl_min ) + $dbl_min;
	}
}

?>
