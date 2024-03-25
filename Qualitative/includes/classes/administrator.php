<?php

/**
 * Class Administrator
 * This class extends the TA class and handles all features and functions
 * specific to the administrator actor of the system. It provides logics
 * for the greengene project and acts directly on user data.
 * @author Jason Xu
 * @package User
 */
class Administrator extends TA
{
	/**
	 * Administrator Constructor
	 * PRE: $p_userId is a valid userid in the User table
	 * POST: TA object is constructed
	 * @param string $p_userId
	 */
	function __construct($p_userId)
	{
		parent::__construct($p_userId);
	}

	function getTraitName($p_traitId)
	{
		global $g_db;

		$result = $g_db->querySelect("SELECT Name
			FROM Trait
			WHERE CourseId=$this->m_courseId
			AND TraitId=$p_traitId");

		if($result == false)
			return false;

		$row = $g_db->fetch($result);
		return $row->Name;
	}

	/**
	 * Create a trait in DB
	 * PRE: $p_name is a trait name
	 * POST: a trait with value $p_name is created in Trait table
	 * @param string $p_name
	 * @return created trait Id or false
	 */
	function createTrait($p_name)
	{
		global $g_db;
		$default_courseId = $this->m_courseId;
		$name = $g_db->sqlString($p_name);

		$sql_query =	"INSERT ".
						"INTO Trait(CourseId,Name) ".
						"VALUES ('". $default_courseId . "','" . $name . "')";
		if(! $g_db->queryCommit($sql_query) )
		{
			(new UserError) -> addError(701);
			return false;
		}

		$recordset = $g_db->querySelect( "SELECT *".
					"FROM Trait".
					"	WHERE Name='".$g_db->sqlString($p_name)."'".
					"	ORDER BY TraitId DESC" );

		$row = $g_db->fetch($recordset);
		return $row->TraitId;
	}


	/**
	 * Add a phenotype
	 * PRE: $p_traitId is a valid TraitId in the Trait table
	 * POST: $p_phenotype is added to the Phenotype table, mapped
	 *       to p_traitId
	 * @param string $p_traitId Id of the trait the phenotype associates
	 * @param string $p_phenotype Phenotype name
	 * @return bool phenotype added
	 */
	function addPhenotype($p_traitId, $p_phenotype)
	{
		global $g_db;
		$sql_query =	"INSERT ".
						"INTO Phenotype(TraitId,Name) ".
						"VALUES ('" . $p_traitId . "','" . $p_phenotype . "')";
		if(! $g_db->queryCommit($sql_query) )
		{
			(new UserError) -> addError(702);
			return false;
		}
		return true;
	}

	/**
	 * Delete a phenotype
	 * PRE: $p_phenotypeId is a valid PhenotypeId in the Phenotype table
	 * POST: The row identified with PhenotypeId $p_phenotypeId is removed
	 *       from the Phenotype table
	 * @param String $p_phenotypeId
	 * @return bool phenotype deleted
	 */
	function deletePhenotype($p_traitId, $p_phenotypeId)
	{
		global $g_db;
		$sql_query =	"DELETE ".
						"FROM Phenotype ".
						"	WHERE PhenotypeId = '" . $p_phenotypeId ."'";
		if(! $g_db->queryCommit($sql_query) )
		{
			(new UserError) -> addError(703);
			return false;
		}
		return true;
	}

	/**
	 * Delete a trait
	 * PRE: $p_traitId is a valid TraitId. No phenotypes are
	 * 		associated with this trait in Phenotype table
	 * POST: The entry of $p_traitId is removed in Trait table
	 * @param String $p_traitId
	 * @return bool trait deleted
	 */
	function deleteTrait($p_traitId)
	{
		global $g_db;
		$sql_query =	"DELETE ".
						"FROM Trait ".
						"	WHERE TraitId = '" . $p_traitId ."'";

		$sql_query2 = 	"DELETE ".
						"FROM Phenotype ".
						"	WHERE TraitId = '" . $p_traitId. "'";

		if( (! $g_db->queryCommit($sql_query) ) ||
			(! $g_db->queryCommit($sql_query2) ) )
		{
			(new UserError) -> addError(704);
			return false;
		}
		return true;
	}
	
	/**
	 * check that progeny values are valid
	 * returns true if values are valid, false otherwise
	 */
	 function validProgenyValues($p_progenyPerMating, $p_maxProgeny) 
	 {
	 		if ($p_progenyPerMating < 0)
			{
				(new UserError) -> addError(751);
				return false;
			}
			if ($p_maxProgeny < 0)
			{
				(new UserError) -> addError(752);
				return false;
			}
			
			//check to make sure that maxprogeny is NOT < progpermating
			if ($p_maxProgeny < $p_progenyPerMating)
			{
				(new UserError) -> addError(765);
				return false;
			}
			
			//all values are OK
			return true;
	 }

	/**
	 * Create a new problem in Master Problem table
	 * PRE: parameters are non-null values
	 * POST: a new problem is created in the MasterProblem table, or
	 *       false is returned
	 * @param string $p_name Problem name
	 * @param string $p_description problem description
	 * @param float $p_gmu1_2 GMU destance between gene 1 and 2
	 * @param float $p_gmu2_3 GMU destance between gene 2 and 3
	 * @param string $p_traitOrder Visual order of the 3 traits
	 * @param int $p_epistasisCode Integer coding of the epistasis
	 * @param string [] $p_arrPhenotypeNames (such as ["Height","Color"])
	 * @param string [][] $p_arrPhenotypes(such as [ 0 => ["Tall","Short"],
	 *												 1 => ["Yellow","Red"] ] )
	 * @return bool problem added
	 */
	function createProblem($p_description, $p_name, $p_gmu1_2, $p_gmu2_3, $p_traitOrder, $p_epistasisCode, $p_arrPhenotypeNames, $p_arrPhenotypes, $p_progenyPerMating, $p_maxProgeny)
	{
		global $g_db;
		$default_courseId = $this->m_courseId;
		//$default_courseId = 4;

		//check the progeny values
		if (! Administrator::validProgenyValues($p_progenyPerMating, $p_maxProgeny)) {
			return false;
		}

		$name 			= $g_db->sqlString($p_name);
		$description 	= $g_db->sqlString($p_description);
		
		// determine the traitnames
		$p_trait1Name = $p_arrPhenotypeNames[0];
		$p_trait2Name = $p_arrPhenotypeNames[1];
		$p_trait3Name = $p_arrPhenotypeNames[2];

		//Call TA's assignPhenotypeLogic method to extract phenotypes
		$phenotypeArray = array();
		// TODO: pass by reference - resolved
		//$this->assignPhenotypeLogic($p_epistasisCode, $p_arrPhenotypes, &$phenotypeArray);
		$phenotypeArray = $this->assignPhenotypeLogic($p_epistasisCode, $p_arrPhenotypes, $phenotypeArray);
		// Values in phenotypeArray is null
		$p_trait1AAName = $phenotypeArray[0][0];
		$p_trait1AbName = $phenotypeArray[0][1];
		$p_trait1bAName = $phenotypeArray[0][2];
		$p_trait1bbName = $phenotypeArray[0][3];

		$p_trait2AAName = $phenotypeArray[1][0];
		$p_trait2AbName = $phenotypeArray[1][1];
		$p_trait2bAName = $phenotypeArray[1][2];
		$p_trait2bbName = $phenotypeArray[1][3];

		$p_trait3AAName = $phenotypeArray[2][0];
		$p_trait3AbName = $phenotypeArray[2][1];
		$p_trait3bAName = $phenotypeArray[2][2];
		$p_trait3bbName = $phenotypeArray[2][3];

		$record_row_values;
		$sql_query;

		// check for null epistasisCode
		if ($p_epistasisCode != -1)
		{
			$record_row_values = "'". $g_db->sqlString($p_description) . "','" . $g_db->sqlString($p_name) . "'," .
								 $p_gmu1_2 . "," . $p_gmu2_3 . ",'" . $g_db->sqlString($p_traitOrder) . "'," .
								 $default_courseId . ",". $p_epistasisCode . ",'" .
								 $g_db->sqlString($p_trait1Name) . "','"  . $g_db->sqlString($p_trait1AAName) . "','" .
								 $g_db->sqlString($p_trait1AbName) . "','" . $g_db->sqlString($p_trait1bAName) . "','" . $g_db->sqlString($p_trait1bbName) . "','" .
								 $g_db->sqlString($p_trait2Name) .  "','" . $g_db->sqlString($p_trait2AAName) . "','" .
								 $g_db->sqlString($p_trait2AbName) . "','" . $g_db->sqlString($p_trait2bAName) . "','" . $g_db->sqlString($p_trait2bbName) . "','" .
								 $g_db->sqlString($p_trait3Name) .  "','" . $g_db->sqlString($p_trait3AAName) . "','" .
								 $g_db->sqlString($p_trait3AbName) . "','" . $g_db->sqlString($p_trait3bAName) . "','" . $g_db->sqlString($p_trait3bbName) . "'," .
								 $p_progenyPerMating . "," . $p_maxProgeny . " ";

			$sql_query = 	"INSERT ".
							"INTO MasterProblem(Description,Name,GMU1_2,GMU2_3,TraitOrder,CourseId,EpistasisCode,".
							"Trait1Name,Trait1AAPhenoName,Trait1AbPhenoName,Trait1bAPhenoName,Trait1bbPhenoName,".
							"Trait2Name,Trait2AAPhenoName,Trait2AbPhenoName,Trait2bAPhenoName,Trait2bbPhenoName,".
							"Trait3Name,Trait3AAPhenoName,Trait3AbPhenoName,Trait3bAPhenoName,Trait3bbPhenoName,".
						 	"ProgenyPerMating,MaxProgeny) ".
						 	"VALUES (" . $record_row_values . ")";
		}
		else
		{
			$record_row_values = "'". $g_db->sqlString($p_description) . "','" . $g_db->sqlString($p_name) . "'," .
								 $p_gmu1_2 . "," . $p_gmu2_3 . ",'" . $g_db->sqlString($p_traitOrder) . "'," .
								 $default_courseId . ",'" .
								 $g_db->sqlString($p_trait1Name) . "','"  . $g_db->sqlString($p_trait1AAName) . "','" .
								 $g_db->sqlString($p_trait1AbName) . "','" . $g_db->sqlString($p_trait1bAName) . "','" . $g_db->sqlString($p_trait1bbName) . "','" .
								 $g_db->sqlString($p_trait2Name) .  "','" . $g_db->sqlString($p_trait2AAName) . "','" .
								 $g_db->sqlString($p_trait2AbName) . "','" . $g_db->sqlString($p_trait2bAName) . "','" . $g_db->sqlString($p_trait2bbName) . "','" .
								 $g_db->sqlString($p_trait3Name) .  "','" . $g_db->sqlString($p_trait3AAName) . "','" .
								 $g_db->sqlString($p_trait3AbName) . "','" . $g_db->sqlString($p_trait3bAName) . "','" . $g_db->sqlString($p_trait3bbName) . "'," .
								 $p_progenyPerMating . "," . $p_maxProgeny . " ";

			$sql_query = 	"INSERT ".
							"INTO MasterProblem(Description,Name,GMU1_2,GMU2_3,TraitOrder,CourseId,".
							"Trait1Name,Trait1AAPhenoName,Trait1AbPhenoName,Trait1bAPhenoName,Trait1bbPhenoName,".
							"Trait2Name,Trait2AAPhenoName,Trait2AbPhenoName,Trait2bAPhenoName,Trait2bbPhenoName,".
							"Trait3Name,Trait3AAPhenoName,Trait3AbPhenoName,Trait3bAPhenoName,Trait3bbPhenoName,".
						 	"ProgenyPerMating,MaxProgeny) ".
						 	"VALUES (" . $record_row_values . ")";

		}

		//echo $record_row_values . "<br>";

		if (! $g_db->queryCommit($sql_query) )
		{
			// UserError::addError(705);
			(new UserError) -> addError(705);
			return false;
		}
		return true;
	}

	/**
	 * Modify an existing problem in Master Problem table
	 * PRE: parameters are non-null values
	 * POST: a new problem is created in the MasterProblem table, or
	 *       false is returned
	 * @param string $p_problemId Problem ID of the target
	 * @param string $p_name Problem name
	 * @param string $p_description problem description
	 * @param float $p_gmu1_2 GMU destance between gene 1 and 2
	 * @param float $p_gmu2_3 GMU destance between gene 2 and 3
	 * @param string $p_traitOrder Visual order of the 3 traits
	 * @param int $p_epistasisCode Integer coding of the epistasis
	 * @param string [] $p_arrPhenotypeNames (such as ["Height","Color"])
	 * @param string [][] $p_arrPhenotypes(such as [ 0 => ["Tall","Short"],
	 *												 1 => ["Yellow","Red"] ] )
	 * @return bool problem added
	 */
	function modifyProblem($p_problemId, $p_description, $p_name, $p_gmu1_2, $p_gmu2_3, $p_traitOrder, $p_epistasisCode, $p_arrPhenotypeNames, $p_arrPhenotypes, $p_progenyPerMating, $p_maxProgeny)
	{ 
//echo "In Admin::modifyProblem ::|".$p_problemId."|".$p_description."|".$p_name."|".$p_gmu1_2."|".$p_gmu2_3."|".$p_traitOrder."|".$p_epistasisCode."|".$p_arrPhenotypeNames."|".$p_arrPhenotypes."|".$p_progenyPerMating."|".$p_maxProgeny."<- endl;<br>";
		global $g_db;
		$default_courseId = $this->m_courseId;
		//$default_courseId = 4;


		//check the progeny values
		if (! Administrator::validProgenyValues($p_progenyPerMating, $p_maxProgeny)) {
			return false;
		}
		
		// Check if the modified target exists
		if( !$this->viewProblem($p_problemId) )
			// UserError::addError(715);
			(new UserError) -> addError(715);

		$name 			= $g_db->sqlString($p_name);
		$description 	= $g_db->sqlString($p_description);

		// determine the traitnames
		$p_trait1Name = $p_arrPhenotypeNames[0];
		$p_trait2Name = $p_arrPhenotypeNames[1];
		$p_trait3Name = $p_arrPhenotypeNames[2];

		//Call TA's assignPhenotypeLogic method to extract phenotypes
		$phenotypeArray;
//check array:
/*
$temp = $phenotypeArray;
while (list($recordIndex,$recordValue) = each($temp)){
	echo $recordIndex. ":" . $recordValue . "<BR>";
	$temp2 = $recordValue;
	while (list($recordIndex2,$recordValue2) = each($temp2)){
		echo "&nbsp;&nbsp;&nbsp;".$recordIndex2. ":" . $recordValue2 . "<BR>";
	}
}
*/

		$phenotypeArray = array();
		$phenotypeArray = $this->assignPhenotypeLogic($p_epistasisCode, $p_arrPhenotypes, $phenotypeArray);
		//$this->assignPhenotypeLogic($p_epistasisCode, $p_arrPhenotypes, &$phenotypeArray);

		$p_trait1AAName = $phenotypeArray[0][0];
		$p_trait1AbName = $phenotypeArray[0][1];
		$p_trait1bAName = $phenotypeArray[0][2];
		$p_trait1bbName = $phenotypeArray[0][3];

		$p_trait2AAName = $phenotypeArray[1][0];
		$p_trait2AbName = $phenotypeArray[1][1];
		$p_trait2bAName = $phenotypeArray[1][2];
		$p_trait2bbName = $phenotypeArray[1][3];

		$p_trait3AAName = $phenotypeArray[2][0];
		$p_trait3AbName = $phenotypeArray[2][1];
		$p_trait3bAName = $phenotypeArray[2][2];
		$p_trait3bbName = $phenotypeArray[2][3];

		$sql_query;

		// check for null epistasisCode
		if ($p_epistasisCode != -1)
		{
			$sql_query = 	"UPDATE MasterProblem ".
							"SET Description = '". $g_db->sqlString($p_description) . "'," .
							"	 Name = '". $g_db->sqlString($p_name) . "'," .
							"	 GMU1_2 = " . $p_gmu1_2 . "," .
							"	 GMU2_3 = " . $p_gmu2_3 . "," .
							"	 TraitOrder = '" . $p_traitOrder . "'," .
							"	 CourseId = '" . $default_courseId . "'," .
							"	 EpistasisCode = " . $p_epistasisCode . "," .
							"	 Trait1Name = '" . $g_db->sqlString($p_trait1Name) . "'," .
							"	 Trait1AAPhenoName = '" . $g_db->sqlString($p_trait1AAName) . "'," .
							"	 Trait1AbPhenoName = '" . $g_db->sqlString($p_trait1AbName) . "'," .
							"	 Trait1bAPhenoName = '" . $g_db->sqlString($p_trait1bAName) . "'," .
							"	 Trait1bbPhenoName = '" . $g_db->sqlString($p_trait1bbName) . "'," .
							"	 Trait2Name = '" . $g_db->sqlString($p_trait2Name) . "'," .
							"	 Trait2AAPhenoName = '" . $g_db->sqlString($p_trait2AAName) . "'," .
							"	 Trait2AbPhenoName = '" . $g_db->sqlString($p_trait2AbName) . "'," .
							"	 Trait2bAPhenoName = '" . $g_db->sqlString($p_trait2bAName) . "'," .
							"	 Trait2bbPhenoName = '" . $g_db->sqlString($p_trait2bbName) . "'," .
							"	 Trait3Name = '" . $g_db->sqlString($p_trait3Name) . "'," .
							"	 Trait3AAPhenoName = '" . $g_db->sqlString($p_trait3AAName) . "'," .
							"	 Trait3AbPhenoName = '" . $g_db->sqlString($p_trait3AbName) . "'," .
							"	 Trait3bAPhenoName = '" . $g_db->sqlString($p_trait3bAName) . "'," .
							"	 Trait3bbPhenoName = '" . $g_db->sqlString($p_trait3bbName) . "'," .
							"	 ProgenyPerMating = " . $p_progenyPerMating . "," .
							" 	 MaxProgeny = ". $p_maxProgeny .
							"		WHERE ProblemId = '" . $g_db->sqlString($p_problemId) . "'";
		}
		else
		{
			$sql_query = 	"UPDATE MasterProblem ".
							"SET Description = '". $g_db->sqlString($p_description) . "'," .
							"	 Name = '". $g_db->sqlString($p_name) . "'," .
							"	 GMU1_2 = " . $p_gmu1_2 . "," .
							"	 GMU2_3 = " . $p_gmu2_3 . "," .
							"	 TraitOrder = '" . $p_traitOrder . "'," .
							"	 CourseId = '" . $default_courseId . "'," .
							"	 EpistasisCode = NULL" . "," .
							"	 Trait1Name = '" . $g_db->sqlString($p_trait1Name) . "'," .
							"	 Trait1AAPhenoName = '" . $g_db->sqlString($p_trait1AAName) . "'," .
							"	 Trait1AbPhenoName = '" . $g_db->sqlString($p_trait1AbName) . "'," .
							"	 Trait1bAPhenoName = '" . $g_db->sqlString($p_trait1bAName) . "'," .
							"	 Trait1bbPhenoName = '" . $g_db->sqlString($p_trait1bbName) . "'," .
							"	 Trait2Name = '" . $g_db->sqlString($p_trait2Name) . "'," .
							"	 Trait2AAPhenoName = '" . $g_db->sqlString($p_trait2AAName) . "'," .
							"	 Trait2AbPhenoName = '" . $g_db->sqlString($p_trait2AbName) . "'," .
							"	 Trait2bAPhenoName = '" . $g_db->sqlString($p_trait2bAName) . "'," .
							"	 Trait2bbPhenoName = '" . $g_db->sqlString($p_trait2bbName) . "'," .
							"	 Trait3Name = '" . $g_db->sqlString($p_trait3Name) . "'," .
							"	 Trait3AAPhenoName = '" . $g_db->sqlString($p_trait3AAName) . "'," .
							"	 Trait3AbPhenoName = '" . $g_db->sqlString($p_trait3AbName) . "'," .
							"	 Trait3bAPhenoName = '" . $g_db->sqlString($p_trait3bAName) . "'," .
							"	 Trait3bbPhenoName = '" . $g_db->sqlString($p_trait3bbName) . "'," .
							"	 ProgenyPerMating = " . $p_progenyPerMating . "," .
							" 	 MaxProgeny = ". $p_maxProgeny .
							"		WHERE ProblemId = '" . $g_db->sqlString($p_problemId) . "'";
		}

//echo $sql_query . "###<br>";

		if (! $g_db->queryCommit($sql_query) )
		{
			(new UserError) -> addError(716);
			return false;
		}

		// updates the modified value in student problem table
		if( ! $g_db->queryCommit("UPDATE StudentProblem" .
						"	SET Modified=2".
						"   WHERE MasterProblemId=".$g_db->sqlString($p_problemId)))
		{
			(new UserError) -> addError(717);
			return false;
		}
		return true;
	}


	/**
	 * Delete a problem in MasterProblem table
	 * PRE: $p_problemId is a valid ProblemId in the MasterProblem table
	 * POST: the problem identified by this id is removed from MasterProblem table
	 * @param $p_problemId MasterProblem id
	 * @return bool problem removed
	 */
	function deleteProblem( $p_problemId )
	{
		global $g_db;

		// first, we must check if there are any problems in StudentProblemTable
		// that makes a reference to this problem

		$sql_query = 	"SELECT UserId ".
						"FROM StudentProblem ".
						"	WHERE MasterProblemId = ". $g_db->sqlString($p_problemId);

		$recordset = $g_db->querySelect($sql_query);
		// if a reference exist, we will not allow the deletion of this
		// problem
		if (!empty($recordset) && $g_db->getNumRows($recordset) > 0)
		{			
			(new UserError) -> addError(718);
			return false;
		}

		$sql_query =	"DELETE ".
						"FROM MasterProblem ".
						"	WHERE ProblemId = '". $g_db->sqlString($p_problemId) . "'";
		if(! $g_db->queryCommit($sql_query) )
		{
			(new UserError) -> addError(706);
			return false;
		}
		return true;
	}

	/**
	 * Create a management user, which includes Admin and TA
	 * PRE: $p_privilegeLevel is a meaningful level to the system
	 *      $p_userId, $p_firstName, $p_lastName are
	 * 		not null values.
	 * POST: a new managment user is created in User table
	 * @param int $p_privilegeLevel New user's privilege level
	 * @param string $p_firstName
	 * @param string $p_lastName
	 * @param string $p_userId
	 * @return bool New management user is created
	 */
	function createManagementUser($p_userId, $p_firstName, $p_lastName, $p_privilegeLvl)
	{
		global $g_db;
		$default_courseId = $this->m_courseId;

		// Check to see if user already exist
		$result = $g_db->querySelect("SELECT UserId
		FROM User
		WHERE UserId='" . $g_db->sqlString($p_userId). "'");

		if($g_db->getNumRows($result) != 0)
		{
			// Adding pre-existing user to a course

			$result = $g_db->querySelect("SELECT CourseId, PrivilegeLvl
				FROM User
				WHERE UserId='" . $g_db->sqlString($p_userId) . "'");
			$row = $g_db->fetch($result);
			// Update current course ID and privilege level

			if (str_contains($row->CourseId, $default_courseId)) {
				(new UserError) -> addError(305);
				return false;
			}

			$updatedCourseID = strval($row->CourseId) . "," . strval($default_courseId);
			$updatedPrivilegeLvl = strval($row->PrivilegeLvl) . "," . strval($p_privilegeLvl);
			$sql_query = "UPDATE User 
				SET CourseId='$updatedCourseID', 
				PrivilegeLvl='$updatedPrivilegeLvl'
				WHERE UserId='$p_userId'";
		}

		else {
			//check user's two entries of password
			$sql_query =	"INSERT INTO User
				(UserId, FirstName, LastName, CourseId, PrivilegeLvl)
				VALUES ('". $g_db->sqlString($p_userId) ."',
					'". $g_db->sqlString($p_firstName) ."',
					'". $g_db->sqlString($p_lastName) ."',
					$default_courseId,
					$p_privilegeLvl)";
		}


		if( !$g_db->queryCommit($sql_query) )
		{
			(new UserError) -> addError(707);
			return false;
		}


		return true;
	}

	/**
	 * Modify an Admin account
	 *
	 * @param string $p_userId admin UserId
	 * @param string $p_firstName admin first name
	 * @param string $p_lastName admin last name
	 * @param int $p_privilegeLvl privilege level
	 * @return bool
	 */
	function modifyManagementUser($p_userId, $p_firstName, $p_lastName, $p_privilegeLvl)
	{
		global $g_db;

		if($p_privilegeLvl != 1 && $p_privilegeLvl != 2)
		{
			(new UserError) -> addError(713);
			return false;
		}

		// If user is in multiple courses
		$sql_query = "SELECT UserId, CourseId, PrivilegeLvl FROM User WHERE UserId='". $p_userId . "'";
		
		$result = $g_db->querySelect($sql_query);
		$userData = $g_db->fetch($result);

		$courseIdArray = explode(',', $userData->CourseId);
		$privilegeLevelArray = explode(',', $userData->PrivilegeLvl);
		$indexOfCourse = array_search($this->m_courseId, $courseIdArray);
		$privilegeLevelArray[$indexOfCourse] = $p_privilegeLvl;
		$updatedPrivilegeLvl = implode(",", $privilegeLevelArray);

		$query = "UPDATE User
			SET FirstName='" . $g_db->sqlString($p_firstName) . "',
			LastName='" . $g_db->sqlString($p_lastName) . "',
			PrivilegeLvl= '$updatedPrivilegeLvl'
			WHERE UserId='" . $g_db->sqlString($p_userId) . "'";

		if($g_db->queryCommit($query) != true)
		{
			(new UserError) -> addError(714);
			return false;
		}

		return true;
	}


	/**
	 * Modify the description of the course
	 * PRE: $p_courseId is a valid courseId in Course table
	 *      $p_desc is not null
	 * POST: Course is changed by description to $p_desc
	 * @param string $p_desc new course description
	 * @param bool modification succeeded
	 */
	function modifyCourse($p_name, $p_desc)
	{
		global $g_db;

		$sql_query = "UPDATE Course
			SET Name='". $g_db->sqlString($p_name) . "',
			Description='" . $g_db->sqlString($p_desc) . "'
			WHERE CourseId = '" . $g_db->sqlString($this->m_courseId) . "'";

		if( !$g_db->queryCommit($sql_query) )
		{
			(new UserError) -> addError(708);
			return false;
		}
		return true;
	}

	/**
	 * Delete a management user
	 * PRE: $p_userId is a valid UserId in User table
	 * POST: user is removed if the privilege allows
	 * @param string $p_userId
	 * @return bool user is deleted
	 */
	function deleteManagementUser($p_userId)
	{
		if($p_userId == $this->m_userId)
		{
			(new UserError) -> addError(709);
			return false;
		}

		global $g_db;


		// If user is in multiple courses
		$sql_query = "SELECT UserId, CourseId, PrivilegeLvl FROM User WHERE UserId='". $p_userId . "'";
		
		$result = $g_db->querySelect($sql_query);
		$userData = $g_db->fetch($result);

		$courseIdArray = explode(',', $userData->CourseId);
		$privilegeLevelArray = explode(',', $userData->PrivilegeLvl);

		// if length of both array > 1
		if (count($courseIdArray) > 1 && count($privilegeLevelArray) > 1) {
			// User courseID = courseID we are looking for
			$indexOfCourse = array_search($this->m_courseId, $courseIdArray);
			// remove a specific element
			unset($courseIdArray[$indexOfCourse]);
			unset($privilegeLevelArray[$indexOfCourse]);
			$updatedCourseID = implode(",", $courseIdArray);
			$updatedPrivilegeLvl = implode(",", $privilegeLevelArray);
			// update the sql query
			$sql_query = "UPDATE User 
				SET CourseId='$updatedCourseID', 
				PrivilegeLvl='$updatedPrivilegeLvl'
				WHERE UserId='$p_userId'";
		}

		// else
		else {
			// If user is only in one course
			$sql_query = "DELETE FROM User
			WHERE UserId = '". $g_db->sqlString($p_userId) . "'";
		}


		if( ! $g_db->queryCommit($sql_query) )
		{
			(new UserError) -> addError(709);
			return false;
		}
		return true;
	}

	/**
	 * Get course admins
	 *
	 * @return recordset
	 */
	function getManagementUsers()
	{
		global $g_db;

		$result = $g_db->querySelect("SELECT UserId, FirstName, LastName, CourseId, PrivilegeLvl
			FROM User
			WHERE CourseId LIKE '%$this->m_courseId%'
			AND ((PrivilegeLvl LIKE '%1%'
			OR PrivilegeLvl LIKE '%2%'))
			ORDER BY UserId");


		return $result;
	}

	/**
	 * Get admin account
	 *
	 * @param int $p_userId user Id
	 * @return recordset
	 */
	function getManagementUser($p_userId)
	{
		global $g_db;

		$result = $g_db->querySelect("SELECT CourseId, FirstName, LastName, PrivilegeLvl
			FROM User
			WHERE PrivilegeLvl IN (1,2)
			AND UserId='" . $g_db->sqlString($p_userId) . "'");

		if($g_db->getNumRows($result) != 1)
		{
			(new UserError) -> addError(712);
			return false;
		}

		return $result;
	}

	/*
	 * Add an error to the logger (local function)
	 */
	 /*
	function addError($p_errorCode)
	{
		// 701 - error creating trait
		// 702 - error adding phynotype
		// 703 - error deleting phynotype
		// 704 - error deleting trait
		// 705 - error creating master problem
		// 706 - error deleting problem
		// 707 - error creating management user
		// 708 - error modifying course
		// 709 - error deleting management user
		// 710 - error changing TA password
		// 711 - TA password not equal
		(new UserError) -> addError($p_errorCode);
	}
	*/
}
?>
