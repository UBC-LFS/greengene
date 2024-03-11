<?php
/**
 * Student Class
 *
 * @author Anoop Shankar, Mark Jia
 * @package User
 */
class Student extends User
{
	var $m_cropName;
	var $m_description;
	var $m_gmu12;				// Distance btw gene 1 to 2
	var $m_gmu23;				// Distance btw gene 2 to 3
	var $m_traitOrder;			// Ordering of traits (eg. '012')

	var $m_traitNames;          // Array of trait names, indexed by trait #
	var $m_phenoNames;          // 2D Array, indexed by trait #, provides the phenotype names of a trait
	var $m_progenyPerMating;
    var $m_maxProgeny;
    var $m_progenyGenerated;	// Total progeny generated so far

    /**
     * Constructor
     *
     * @param string $p_userId userID of a student (userId must be in the StudentProblem table)
     */
	// function Student($p_userId)
	function __construct($p_userId)
	{
		// parent::User($p_userId);
		parent::__construct($p_userId);
		global $g_db;
	}


	// NEW FUNCTION - 2024-02-26
	/**
     * Get the student problem based off userID AND courseID
     *
     * @param string $p_userId
     */
	function getStudentProblem($p_userId) {
		global $g_db;

		$sqlQuery = "SELECT Name, Description, GMU1_2, GMU2_3, TraitOrder,
						Trait1Name, Trait1AAPhenoName, Trait1AbPhenoName, Trait1bAPhenoName, Trait1bbPhenoName,
						Trait2Name, Trait2AAPhenoName, Trait2AbPhenoName, Trait2bAPhenoName, Trait2bbPhenoName,
						Trait3Name, Trait3AAPhenoName, Trait3AbPhenoName, Trait3bAPhenoName, Trait3bbPhenoName,
						ProgenyPerMating, MaxProgeny, ProgenyGenerated
					FROM StudentProblem
					WHERE UserId='" . $g_db->sqlString($p_userId) . "'" . "AND CourseId='" . $this->m_courseId . "'";
		$studentRecord = $g_db->querySelect($sqlQuery);

//		echo "debugging: query:" . $g_db -> getNumRows($studentRecord);


		// var_dump("TESTING NEW FUNCTION");
		// var_dump($sqlQuery);

		$problem = $g_db -> fetch($studentRecord);

		if (is_object($problem)) {
			$this->m_cropName = $problem -> Name;
			$this->m_description = $problem -> Description;
			$this->m_gmu12 = $problem -> GMU1_2;
			$this->m_gmu23 = $problem -> GMU2_3;
			$this->m_traitOrder = $problem -> TraitOrder;

			$this->m_traitNames = array($problem->Trait1Name,
										$problem->Trait2Name,
										$problem->Trait3Name);

			$this->m_phenoNames = array(array($problem->Trait1AAPhenoName,
											$problem->Trait1AbPhenoName,
											$problem->Trait1bAPhenoName,
											$problem->Trait1bbPhenoName),
										array($problem->Trait2AAPhenoName,
											$problem->Trait2AbPhenoName,
											$problem->Trait2bAPhenoName,
											$problem->Trait2bbPhenoName),
										array($problem->Trait3AAPhenoName,
											$problem->Trait3AbPhenoName,
											$problem->Trait3bAPhenoName,
											$problem->Trait3bbPhenoName));

			$this->m_progenyPerMating = $problem->ProgenyPerMating;
			$this->m_maxProgeny = $problem->MaxProgeny;
			$this->m_progenyGenerated = $problem->ProgenyGenerated;
			//echo "<p>Student $this->m_userId created! Prog per mating = $this->m_progenyPerMating";
		}
	}

    /**
     * Get genes for crosses requiring longer gene sequences
     *
     * @param int $p_crossNum
     *
     * @return string Full gene sequences of the specified cross
     */
	function getLongerGenes($p_crossNum)
	{
		global $g_db;

    	$longGeneRecord = $g_db->querySelect("SELECT CrossNum, UserId, GeneSequences
											  FROM LongerGeneSequences
											  WHERE CrossNum=$p_crossNum
											  AND UserId='" . $g_db->sqlString($this->m_userId) . "'");

    	while ($curGene = $g_db->fetch($longGeneRecord))	// Combine sequences
    	{
    		$genes .= $curGene->GeneSequences;
    	}

		return $genes;
	}

    /**
     * Get the progeny information of a cross (or all crosses) belonging to this student.
     * If no cross number is specified (or an invalid one), the latest cross will be used.
     *
     * @param mixed $p_crossNum optional cross number or 'All' or 'Latest'
     *
     * @return Cross Cross object storing information about the progeny in the cross(es)
     */
	function getProgeny($p_crossNum='Latest')
	{
		global $g_db;
		
		$sql = "SELECT CrossNum, GeneSequences, UNIX_TIMESTAMP(CreationDate) AS CreationDate,
				PollenCrossNum, PollenPlantNum, PollenGene, SeedCrossNum,
				SeedPlantNum, SeedGene
				FROM `Cross`
				WHERE UserId='" . $g_db->sqlString($this->m_userId) . "'" . "AND CourseId='" . $this->m_courseId . "'";

		
		// var_dump("Works if user is in 1 course, once added to 2nd course, it displays the problem for the 2nd course instead");
		// var_dump("assign problem page also displaying problem from other courses");

		// var_dump("FOUND ERROR: When we assign a problem to a student in another course, it deletes the previous problem!");

		// var_dump($sql);
		// var_dump("Testing course ID grab");

		if ($p_crossNum == 'Latest')				// Find latest cross number
			$p_crossNum = $this->getCrossCount();

		// Append specified cross number(s) to query
		if(is_numeric($p_crossNum))					// Use specified number
			$sql .= "AND CrossNum=$p_crossNum";
		elseif ($p_crossNum == 'All')				// Show all crosses
			$sql .= "ORDER BY CrossNum";
		else										// Invalid cross number
		{
			(new UserError) -> addError(400);
			$sql .= "AND CrossNum=" . $this->getCrossCount();	// Use latest
		}

		// var_dump($sql);

		$crossRecord = $g_db->querySelect($sql);

		// var_dump($crossRecord);

		$crossData = new Cross($this->m_traitOrder, $this->m_traitNames, $this->m_phenoNames);

		while ($cross = $g_db->fetch($crossRecord))
		{
    		$genes = $cross->GeneSequences;

    		if (empty($genes)) // Requires longer gene sequence
    			$genes = $this->getLongerGenes($cross->CrossNum);

			$crossData->addCross($cross->CrossNum,
								 $cross->CreationDate,
								 $genes,
								 $cross->PollenCrossNum,
								 $cross->PollenPlantNum,
    		                     $cross->PollenGene,
    		                     $cross->SeedCrossNum,
    		                     $cross->SeedPlantNum,
    		                     $cross->SeedGene);
		}

		return $crossData;
	}
//
//	/**
//	 * Get the progeny informaion of the latest cross.
//	 *
//	 * @return Cross cross object storing information about the progeny of the latest cross
//	 */
//	function getLatestProgeny()
//	{
//		return $this->getProgeny($this->getCrossCount());
//	}

	/**
	 * Determine the number of crosses associated with this student.
	 *
	 * @return int Number of crosses of this student
	 */
	function getCrossCount()
	{
		global $g_db;

	    $crossRecord = $g_db->querySelect("SELECT COUNT(*) AS CrossCount
	    								   FROM `Cross`
										   WHERE UserId='" . $g_db->sqlString($this->m_userId) . "'" . "AND CourseId='" . $this->m_courseId . "'");
		$temp = $g_db->fetch($crossRecord);
		return $temp->CrossCount;
	}


    /**
     * Create a new cross of progeny.
     *
     * @param int $p_pollenCrossNum
     * @param int $p_pollenPlantNum plant number in the cross
     * @param int $p_seedCrossNum
     * @param int $p_seedPlantNum plant number in the cross
     *
     * @return int Cross number corresponding to the new progeny
     */
	function performCross($p_pollenCrossNum, $p_pollenPlantNum, $p_seedCrossNum, $p_seedPlantNum)
	{
		global $g_db;

	    // Determine next cross number
		$nextCrossNum = $this->getCrossCount() + 1;

	    // Get gene sequences of parents
		$sqlQuery = "SELECT CrossNum, GeneSequences
				FROM `Cross`
				WHERE (UserId='" . $g_db->sqlString($this->m_userId) . "'
				AND (CrossNum=$p_pollenCrossNum OR CrossNum=$p_seedCrossNum) AND CourseId='" . $this->m_courseId . "')";


		// var_dump($sqlQuery);

		$geneRecord = $g_db->querySelect($sqlQuery);


		$gene1 = $g_db->fetch($geneRecord);
		// Need to check if the plants come from the same cross: if they do, there will be only 1 row in the recordSet
		$gene2 = $g_db->getNumRows($geneRecord) > 1 ? $g_db->fetch($geneRecord) : $gene1;

		if ($gene1->CrossNum == $p_pollenCrossNum)  // Gene1 is for the pollen plant
		{
			// Gene1 is the pollen gene sequence(remember, a gene sequence is represented by four consecutive numbers)
			$pollenGeneSeq = empty($gene1->GeneSequences) ? $this->getLongerGenes($gene1->CrossNum) : $gene1->GeneSequences;
			$seedGeneSeq   = empty($gene2->GeneSequences) ? $this->getLongerGenes($gene2->CrossNum) : $gene2->GeneSequences;
		}
		else
		{
			// Gene1 is the seed gene sequence
			$seedGeneSeq   = empty($gene1->GeneSequences) ? $this->getLongerGenes($gene1->CrossNum) : $gene1->GeneSequences;
			$pollenGeneSeq = empty($gene2->GeneSequences) ? $this->getLongerGenes($gene2->CrossNum) : $gene2->GeneSequences;
		}
		
		//update the student object's progeny generated member variable
		//because the user object from the Session may be out of sync with the DB
		$sqlQuery = "SELECT ProgenyGenerated
                     FROM `StudentProblem`
					 WHERE UserId='" . $g_db->sqlString($this->m_userId) . "'" . "AND CourseId='" . $this->m_courseId . "'";
		
		$studentSet = $g_db->querySelect($sqlQuery);
		
		
		$studentRow = $g_db->fetch($studentSet);
		$this->m_progenyGenerated =  $studentRow->ProgenyGenerated;
		//echo "<p> DEBUGGING (performCross): ProgenyGenerated before Cross performed = ".$this->m_progenyGenerated;
		
		//check that progenygenerated has not been exceeded						
		if (($this->m_progenyGenerated + $this->m_progenyPerMating) > $this->m_maxProgeny)
		{
			(new UserError) -> addError(410);
			return false;
			
		} 
		else
		{
		// Call generateProgeny of the Cross class to perform the actual cross (return the resulting cross number)
		// $success = Cross::generateProgeny($this->m_userId,
		// 					   $p_pollenCrossNum, $p_pollenPlantNum, $pollenGeneSeq,
		// 					   $p_seedCrossNum,   $p_seedPlantNum,   $seedGeneSeq,
		// 					   $this->m_gmu12, $this->m_gmu23, $this->m_progenyPerMating, $nextCrossNum);
							   
		$crossData = new Cross($this->m_traitOrder, $this->m_traitNames, $this->m_phenoNames);
		$success = $crossData -> generateProgeny($this->m_userId,
									$p_pollenCrossNum, $p_pollenPlantNum, $pollenGeneSeq,
									$p_seedCrossNum,   $p_seedPlantNum,   $seedGeneSeq,
									$this->m_gmu12, $this->m_gmu23, $this->m_progenyPerMating, $nextCrossNum);
			if ($success)
			{
				$this->m_progenyGenerated = $this->m_progenyGenerated + $this->m_progenyPerMating;
				
				//echo "<p> DEBUGGING (performCross) : ProgenyGenerated (total after cross performed):".$this->m_progenyGenerated;
				
				$sqlQuery = "UPDATE `StudentProblem` 
							 SET `ModificationDate` = NOW( ) ,
							`ProgenyGenerated` = ". $this->m_progenyGenerated .
							 " WHERE `UserId` = '". $g_db->sqlString($this->m_userId) . "'" . " AND CourseId='" . $this->m_courseId . "'";

				//echo "<p>DEBUGGING (performCross) SQL Query: ".$sqlQuery;
				

				$db_success = $g_db->queryCommit($sqlQuery);
				
				//echo "<p> DB SUCCESS : ".$db_success;

				if ($db_success) 
				{
					return $nextCrossNum;
				}
				else
				{
					(new UserError) -> addError(420);
					return false;
				}
			}
			else 
			{
				(new UserError) -> addError(420);
				return false;
			}
		}
	}

	function getCropName()
	{
		return $this->m_cropName;
	}

	function getProblemDescription()
	{
		return $this->m_description;
	}
}
?>
