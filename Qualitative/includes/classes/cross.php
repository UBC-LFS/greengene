<?php
/**
 * Cross class
 *
 * @author Mark Jia
 * @package User
 */
class Cross
{
	var $m_traitOrder;  // Order to display traits, eg. '012', '210', '102'
	var $m_phenoNames;  // 2D array of phenotype names, keyed to trait number
	var $m_traitNames;  // Array of trait names, keyed to trait number

	var $m_crosses;     // 2D array of cross records, keyed to cross number

    /**
     * Constructor
     *
     * PRE:  Parameters passed are valid
     * POST: Member variables are initialized and set
     *
     * @param string $p_traitOrder Ordering of traits, start at 0, eg. "012"
     * @param string[] $p_traitNames Names of traits, keyed to trait number
     * @param string[][] $p_phenoNames 2D array of phenotype names, keyed to trait number
     */
    // function Cross($p_traitOrder, $p_traitNames, $p_phenoNames)
	function __construct($p_traitOrder, $p_traitNames, $p_phenoNames)
    {
        $this->m_traitOrder = $p_traitOrder;
        $this->m_traitNames = $p_traitNames;
        $this->m_phenoNames = $p_phenoNames;

        $this->m_crosses = array();
    }

    /**
     * Record a new cross. Adds it to the array of crosses keyed to the cross number.
     *
     * PRE:  Parameter values are valid
     * POST: Parameter values are stored in object
     *
     * @param int $p_crossNum
     * @param string $p_createDate
     * @param string $p_genes Gene sequence of all plants in this cross
     *
     * @param int $p_pollenCrossNum
     * @param int $p_pollenPlantNum Position in the pollen gene sequence of the pollen gene
     * @param string $p_pollenGene
     *
     * @param int $p_seedCrossNum
     * @param int $p_seedPlantNum Position in the seed gene sequence of the seed gene
     * @param string $p_seedGene
     */
    function addCross($p_crossNum, $p_creationDate, $p_genes,
					  $p_pollenCrossNum, $p_pollenPlantNum, $p_pollenGene,
					  $p_seedCrossNum, $p_seedPlantNum, $p_seedGene)
	{
        $this->m_crosses[$p_crossNum] = array("Date"  => $p_creationDate,
                                              "Genes" => $p_genes,
                                              "PollenCrossNum" => $p_pollenCrossNum,
                                              "PollenPlantNum" => $p_pollenPlantNum,
                                              "PollenGene"     => $p_pollenGene,
                                              "SeedCrossNum"   => $p_seedCrossNum,
                                              "SeedPlantNum"   => $p_seedPlantNum,
                                              "SeedGene"       => $p_seedGene);
    }

    /**
     * Generate a new cross and add it to the database.
     *
     * PRE:  Parameters values are valid
     * POST: Appropriate progeny are generated based on genetic info specified in parameter.
     *       Generated cross record is added to the database.
     *
     * @param string $p_userId
     *
     * @param int $p_pollenCrossNum
     * @param int $p_pollenPlantNum Position in the pollen gene sequence of the pollen gene
     * @param string $p_pollenGene Full gene sequence of all plants in the cross
     *
     * @param int $p_seedCrossNum
     * @param int $p_seedPlantNum Position in the seed gene sequence of the seed gene
     * @param string $p_seedGene Full gene sequence of all plants in the cross
     *
     * @param flaot $p_gmu12 Distance btw genes 1 & 2
     * @param float $p_gmu23 Distance btw genes 2 & 3
     * @param int $p_numProgeny Number of progeny to generate
     * @param int $p_crossNum
     */
	function generateProgeny($p_userId,
							 $p_pollenCrossNum, $p_pollenPlantNum, $p_pollenGene,
							 $p_seedCrossNum, $p_seedPlantNum, $p_seedGene,
	                         $p_gmu12, $p_gmu23, $p_numProgeny, $p_crossNum)
	{
		$gmus = array(50, $p_gmu12, $p_gmu23, 50);  // 50:50 for traits 1 & 4

		// Locate gene of target plant in cross, trim to remove optional gene 4 if necessary
		$pollen = rtrim(substr($p_pollenGene, ($p_pollenPlantNum - 1) * 4, 4));
		$seed   = rtrim(substr($p_seedGene, ($p_seedPlantNum - 1) * 4, 4));

		$geneCount = strlen($pollen);  // Number of traits 3 or 4

		// Construct pollen and seed matrices
		$pollenMatrix = array();	// 2D array of alleles for pollen
		$seedMatrix   = array();	// 2D array of alleles for seed

		for ($i = 0; $i < $p_numProgeny; $i++)  // Initialize empty 2D arrays
		{
			array_push($pollenMatrix, array());
			array_push($seedMatrix, array());
		}

		// Populate matrices of alleles for each parent
		Cross::initAlleles($pollen, $gmus, $pollenMatrix, $p_numProgeny, 0, $geneCount);
		Cross::initAlleles($seed, $gmus, $seedMatrix, $p_numProgeny, 0, $geneCount);

//		echo "<br> pollen matrix: "; print_r($pollenMatrix);
//		echo "<br> seed matrix: "; print_r($seedMatrix); echo "<br>";

		shuffle($pollenMatrix);
		shuffle($seedMatrix);

        // Merge to form gene sequence

		$sequencesArr = array();

		for ($i = 0; $i < $p_numProgeny; $i++)      // Process each plant
		{
			$sequence = '';				// Sequence for current single plant

			for ($f = 0; $f < $geneCount; $f++)    // Set a plant's sequence
			{
				if ($pollenMatrix[$i][$f])			// Allele from pollen is dominant
				{
					if ($seedMatrix[$i][$f])		// Allele from seed is dominant
						$sequence .= '0';			// AA
					else
						$sequence .= '1';			// Ab
				}
				else
				{
					if ($seedMatrix[$i][$f])		// Allele from pollen is dominant
						$sequence .= '2';			// bA
					else
						$sequence .= '3';			// bb
				}
			}

			if ($geneCount < 4)    // Only three genes, pad a space
			    $sequence .= ' ';

			array_push($sequencesArr, $sequence);
		}

		// Combine sequences into arrays of 50 progenies

        $sequenceGroups = array();

		for ($i = 0; $i < $p_numProgeny;)
		{
		    $sequence = '';

		    for ($f = 0; $f < 50; $f++)
		    {
		        $sequence .= $sequencesArr[$i++];

		        if ($i > $p_numProgeny)
		            break;
		    }

		    array_push($sequenceGroups, $sequence);
		}

		// Add new cross into database

		global $g_db;

		$firstSequence = sizeof($sequenceGroups) == 1 ? $sequenceGroups[0] : NULL;

		// var_dump($g_db);
		var_dump("courseID not in this");

		// get courseId from function arg?
		// get courseId from URL?
		$user = (new Security) -> getUser();
		// var_dump($user->m_courseId);

		// return false;

		$success = $g_db->queryCommit("INSERT INTO `Cross` (CrossNum, UserId,
										         PollenCrossNum, PollenPlantNum, PollenGene,
										         SeedCrossNum, SeedPlantNum, SeedGene,
											     GeneSequences, CourseId)
							VALUES ('" . $g_db->sqlString($p_crossNum)			. "', '" .
										 $g_db->sqlString($p_userId)			. "', '" .
										 $g_db->sqlString($p_pollenCrossNum)	. "', '" .
										 $g_db->sqlString($p_pollenPlantNum)	. "', '" .
										 $g_db->sqlString($pollen)				. "', '" .
										 $g_db->sqlString($p_seedCrossNum)		. "', '" .
										 $g_db->sqlString($p_seedPlantNum)		. "', '" .
										 $g_db->sqlString($seed)				. "', '" .
										 $g_db->sqlString($firstSequence)		. "', '" .
										 $user->m_courseId . "')");

		if ($success && is_null($firstSequence))    // Requires LongerGeneSequences
        {
    		foreach ($sequenceGroups as $sequence)  // Combine into one query
    		{
    		    $values .= ",('" . $g_db->sqlString($p_userId)		. "', '" .
    		                       $g_db->sqlString($p_crossNum)	. "', '" .
    		                       $g_db->sqlString($sequence)		. "')";
    		}

    		if (!$g_db->queryCommit("INSERT INTO LongerGeneSequences (UserId, CrossNum, GeneSequences)
    						    VALUES " . substr($values, 1)))
  				$success = false;
        }
        
        return $success;
	}

    /**
     * An algorithm that generates the set of alleles that would get passed on from a single parent.
     * Algorithm is tail recursive.
     *
     * PRE:  Parameters values are valid (valid gene sequences, alleles values are [0,3]).
     * POST: 2D array of alleles is set. The ordering of the alleles are NOT randomized.
     *
     * @param string $p_geneSeq The gene sequence of the parent plant to select alleles from
     * @param float[] $p_gmus Distance between traits
     *
     * @param string[][] &$p_sequence The collection of alleles being recursively set
     * @param int $p_sequenceCount The number of progeny in the sequences
     *
     * @param int $p_curGeneIdx Current position in the parent gene sequence
     * @param int $p_geneLength Length of parent gene sequence
     */
	function initAlleles($p_geneSeq, $p_gmus, &$p_sequence, $p_sequenceCount, $p_curGeneIdx, $p_geneLength)
	{
        if ($p_curGeneIdx == $p_geneLength)
            return;

		$gene = $p_geneSeq[$p_curGeneIdx];
		
		$allele1 = $gene == '0' || $gene == '1';    // Dominance of allele 1
		$allele2 = $gene == '0' || $gene == '2';    // Dominance of allele 2
		
		if ($p_curGeneIdx > 0)
		{
			// Account for linkage by moving some rows from the bottom to the top
			$linkageRows = ($p_gmus[$p_curGeneIdx] * $p_sequenceCount) / 200;
			
			for ($i = 0; $i < $linkageRows; $i++)	// Move last row to beginning
				array_unshift($p_sequence, array_pop($p_sequence));
		}
	
        if ($allele1 == $allele2)       // Dominance is equal
		{
			$numAllele1Idx = $p_sequenceCount;
		}
		else							// Determine a random factor (within 1%) to split the dominance
		{
        	$maxDeviationSq = ($p_sequenceCount * $p_sequenceCount) / 10000;
        	$rndDeviation = sqrt(rand(0, $maxDeviationSq));
			
			if (rand(-1, 1) < 0)
				$rndDeviation *= -1;
			
          	//echo "Deviation: ($maxDeviationSq, $rndDeviation)<br>\n";
      	
        	$numAllele1Idx = $p_sequenceCount / 2 + $rndDeviation;
        }
		
		// Set allele 1 for [0, $numAllele1Idx - 1]
		for ($i = 0; $i < $numAllele1Idx; $i++)
			array_push($p_sequence[$i], $allele1);
		
		// Set allele 2 for [$numAllele1Idx, $p_sequenceCount - 1]
		for ($i = $numAllele1Idx; $i < $p_sequenceCount; $i++)
			array_push($p_sequence[$i], $allele2);
		
		// Tail recurse
		Cross::initAlleles($p_geneSeq, $p_gmus, $p_sequence, $p_sequenceCount, $p_curGeneIdx + 1, $p_geneLength);
	}
}

// Testing:
//$cross = Cross::generateProgeny('mjia',
//								1, 1, '113 ',
//								2, 1, '113 ',
//								50, 50, 100, 3);
//								
//foreach ($cross as $curCross)
//{
//	for ($i = 0; $i < strlen($curCross); $i += 4)
//	{
//		$gene1 = $curCross[$i];
//		
//		switch ($gene1)
//		{
//			case 0:
//				$count0++;
//				break;
//			case 1:
//				$count1++;
//				break;
//			case 2:
//				$count2++;
//				break;
//			case 3:
//				$count3++;
//				break;
//		}
//		
////		echo substr($cross[0], $i, 4) . "<br>\n";
//	}
//}
//
//echo "$count0, $count1, $count2, $count3";

?>