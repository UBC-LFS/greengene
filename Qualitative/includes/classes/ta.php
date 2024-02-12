<?php
/**
 * TA Class
 *
 * @author Gary Poon
 * @package User
 */
class TA extends User
{
	/**
	 * TA Constructor
	 * PRE: $p_userId is a valid userid in the User table
	 * POST: TA object is constructed
	 * @param string $p_userId
	 */
	// function TA($p_userId)
	function __construct($p_userId)
	{
		// parent::User($p_userId);
		parent::__construct($p_userId);
	}

	/**
	 * viewProblem: This retrieves one problem record from the MasterProblem table in DB
	 * PRE: $p_problemId is a non-null value
	 * POST: a recordset containing the problem with $p_problemId
	 * @param int $p_problemId
	 * @return recordset
	 */
	function viewProblem($p_problemId)
	{
		global $g_db;

		$sql_query = "SELECT ProblemId,Description,Name,GMU1_2,GMU2_3,TraitOrder,UNIX_TIMESTAMP(ModificationDate) AS FormattedTime,CourseId,EpistasisCode,".
					 "Trait1Name,Trait1AAPhenoName,Trait1AbPhenoName,Trait1bAPhenoName,Trait1bbPhenoName,".
					 "Trait2Name,Trait2AAPhenoName,Trait2AbPhenoName,Trait2bAPhenoName,Trait2bbPhenoName,".
					 "Trait3Name,Trait3AAPhenoName,Trait3AbPhenoName,Trait3bAPhenoName,Trait3bbPhenoName,".
					 "ProgenyPerMating,MaxProgeny ".
					 "FROM MasterProblem ".
					 "	WHERE ProblemId = $p_problemId";

		return $g_db->querySelect($sql_query);
	}

	/**
	 * createStudent: This inserts a record into the User table of DB
	 * PRE: $p_userId, $p_firstName, $p_lastName are non-null values
	 * POST: bool indicating whether the action was succesful or not
	 * @param string $p_userId
	 * @param string $p_firstName
   	 * @param string $p_lastName
   	 * @param int $p_courseId
	 * @return bool
	 */
	function createStudent($p_userId, $p_firstName, $p_lastName)
	{
		global $g_db;

		$default_courseId = $this->m_courseId;


		$privilege_lvl = 3; // level 3 for student

		$record_row_values = "'". $g_db->sqlString($p_userId) . "'," . $default_courseId . ","  . $privilege_lvl . "," .
							 "'" . $g_db->sqlString($p_firstName) . "','" . $g_db->sqlString($p_lastName). "'";


		// checks if user already exist
		$result = $g_db->querySelect("SELECT UserId
			FROM User
			WHERE UserId='" . $g_db->sqlString($p_userId) . "'");
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
			$updatedPrivilegeLvl = strval($row->PrivilegeLvl) . "," . strval($privilege_lvl);
			$sql_query = "UPDATE User 
				SET CourseId='$updatedCourseID', 
				PrivilegeLvl='$updatedPrivilegeLvl'
				WHERE UserId='$p_userId'";
		}
		// ^^
		else {
			$sql_query =	"INSERT ".
							"INTO User (UserId,CourseId,PrivilegeLvl,FirstName,LastName) ".
							"VALUES (" . $record_row_values . ")";
		}

		if ($g_db->queryCommit($sql_query)!=true)
		{
			(new UserError) -> addError(600);
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
	 * modifyStudent: This updates a record from the User table of DB
	 * It may also update the progenyPerMating and maxProgeny of the
	 * StudentProblem of DB
	 * PRE: $p_userId, $p_firstName, $p_lastName are non-null values
	 * POST: bool indicating whether the action was succesful or not
	 * @param string $p_userId
	 * @param string $p_firstName
   	 * @param string $p_lastName
   	 * @param int $p_progenyPerMating
   	 * @param int $p_maxProgeny
	 * @return bool
	 */
	function modifyStudent($p_userId, $p_firstName, $p_lastName, $p_progenyPerMating=-1, $p_maxProgeny=-1)
	{
		global $g_db;

		$sql_query = 	"UPDATE User ".
						"SET FirstName = '". $g_db->sqlString($p_firstName) . "'," .
						"	 LastName = '" . $g_db->sqlString($p_lastName) . "' " .
						"		WHERE UserId = '" . $g_db->sqlString($p_userId) . "'";

		if ($g_db->queryCommit($sql_query)!= true)
		{
			(new UserError) -> addError(601);
			return false;
		}

		// update progenypermating and maxprogeny, if necessary
		if ($p_progenyPerMating != -1 && $p_maxProgeny != -1)
		{
			//echo "<p> DEBUGGING: inside progenyvalues updating : $p_progenyPerMating, $p_maxProgeny";

			if (TA::validProgenyValues($p_progenyPerMating, $p_maxProgeny))
			{

				$sql_query = 	"UPDATE StudentProblem ".
							"SET ProgenyPerMating = " . $p_progenyPerMating . ",".
							"	 MaxProgeny = " . $p_maxProgeny . ",".
							"	 Modified=1".
							"		WHERE UserId = '" . $g_db->sqlString($p_userId) . "'";

				//echo "<p> DEBUGGING: $sql_query";

				if ($g_db->queryCommit($sql_query) != true)
				{
					(new UserError) -> addError(608);
					return false;
				}
			}
			else
			{
				//the progeny values entered were invalid
				return false;
			}
		}

		return true;
	}

	/**
	 * deleteStudent: This deletes a record from the User table of DB
	 * It will also remove any entries containing $p_userId from the StudentProblem,
	 * Cross, and LongerGeneSequences table of the DB
	 * PRE: $p_userId is a non-null value
	 * POST: bool indicating whether the action was succesful or not
	 * @param string $p_userId
	 * @return bool
	 */
	function deleteStudent($p_userId)
	{
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
			$sql_query = 	"DELETE ".
			"FROM User ".
			"	WHERE UserId = '". $g_db->sqlString($p_userId) . "'";
		}

		if ($g_db->queryCommit($sql_query)!= true)
		{
			(new UserError) -> addError(602);
			return false;
		}


		// ok, now we've also got to delete the problem from the StudentProblem table
		// let's check to see if an entry even exist first
		$sql_query = 	"SELECT UserId ".
						"FROM StudentProblem ".
						"	WHERE UserId = '". $g_db->sqlString($p_userId) . "'";

		$recordset = $g_db->querySelect($sql_query);
		if ( !empty($recordset) )
		{
			$sql_query = 	"DELETE ".
							"FROM StudentProblem ".
							"	WHERE UserId = '". $g_db->sqlString($p_userId) . "'";

			if ($g_db->queryCommit($sql_query)!= true)
			{
				(new UserError) -> addError(609);
				return false;
			}
		}

		if (TA::deleteCrossSimulation($p_userId) != true)
		{
			return false;
		}

		return true;
	}

	/**
	 * assignPhenotypeLogic: A helper function for assignProblem and reassignProblem
	 * This function helps to determine what each of trait_AA, trait_Ab, trait_bA, trait_bb,
	 * (where _ is from 1 to 3), based on the information given by $p_epistasisRatio and $p_arrPhenotypes
	 * PRE: $p_epistasisRatio and $p_arrPhenotypes are non-null values
	 * POST: string [][] array
	 * @param int $p_epistasisCode
	 * @param string [][] $p_arrPhenotypes
	 * @return string [][] $phenotypeArray
	 */
	function assignPhenotypeLogic($p_epistasisCode, $p_arrPhenotypes, $p_phenotypeArray = null)
	{
		// return structure
		// phenotypeArray
		// (
		// 		[0] => ($trait1AA,$trait1Ab,$trait1bA,$trait1bb)
		// 		[1] => ($trait2AA,$trait2Ab,$trait2bA,$trait2bb)
		// 		[2] => ($trait3AA,$trait3Ab,$trait3bA,$trait3bb)
		// )

//check array:
/*
$temp = $p_arrPhenotypes;
while (list($recordIndex,$recordValue) = each($temp)){
	echo $recordIndex. ":" . $recordValue . "<BR>";
	$temp2 = $recordValue;
	while (list($recordIndex2,$recordValue2) = each($temp2)){
		echo "&nbsp;&nbsp;&nbsp;".$recordIndex2. ":" . $recordValue2 . "<BR>";
	}
}
*/
		$p_phenotypeArray = array (
			0 => array("","","",""),
			1 => array("","","",""),
			2 => array("","","","")
		);

		global $g_epistaticRatios;
		$epistasisRatio = "";
		if ($p_epistasisCode != -1)
		{
			$epistasisRatio = $g_epistaticRatios[$p_epistasisCode];
		}

		$current_trait = array();
		$pheno_num = 0;
		for ( $i = 0; $i < 3; $i++)
		{
			$current_trait = $p_arrPhenotypes[$i];
			if (is_array($current_trait)) {
				$pheno_num = count($current_trait);
			}

			// go in here if we are processing the first 2 traits,
			// or if we are in the third trait but we don't have
			// epistasis
			if ($i != 2 || $epistasisRatio == '')
			{
				// 3 means no dominance, it will have mixture
				if ($pheno_num == 3)
				{
					//echo "First trait info..<p>";
					//echo $current_trait[0] . " " . $current_trait[2] . " " . $current_trait[1];
					$p_phenotypeArray[$i][0] = $current_trait[0];
					$p_phenotypeArray[$i][1] = $current_trait[2];
					$p_phenotypeArray[$i][2] = $current_trait[2];
					$p_phenotypeArray[$i][3] = $current_trait[1];
				}
				// else assume dominance
				else
				{
					$p_phenotypeArray[$i][0] = $current_trait[0];
					$p_phenotypeArray[$i][1] = $current_trait[0];
					$p_phenotypeArray[$i][2] = $current_trait[0];
					$p_phenotypeArray[$i][3] = $current_trait[1];
				}
			}
			// going in here means we are processing the third trait
			// with epistasis
			else
			{
				if (strcmp($epistasisRatio,$g_epistaticRatios[0]) == 0)
				{
					// a ratio of 9:3:3:1
					$p_phenotypeArray[$i][0] = $current_trait[0];
					$p_phenotypeArray[$i][1] = $current_trait[1];
					$p_phenotypeArray[$i][2] = $current_trait[2];
					$p_phenotypeArray[$i][3] = $current_trait[3];
				}
				else if (strcmp($epistasisRatio,$g_epistaticRatios[1])== 0)
				{
					// a ratio of 9:3:4
					$p_phenotypeArray[$i][0] = $current_trait[0];
					$p_phenotypeArray[$i][1] = $current_trait[1];
					$p_phenotypeArray[$i][2] = $current_trait[2];
					$p_phenotypeArray[$i][3] = $current_trait[2];
				}
				else if (strcmp($epistasisRatio,$g_epistaticRatios[2]) == 0)
				{
					// a ratio of 9:7
					$p_phenotypeArray[$i][0] = $current_trait[0];
					$p_phenotypeArray[$i][1] = $current_trait[1];
					$p_phenotypeArray[$i][2] = $current_trait[1];
					$p_phenotypeArray[$i][3] = $current_trait[1];
				}
				else if (strcmp($epistasisRatio,$g_epistaticRatios[3])== 0)
				{
					// a ratio of 12:3:1
					$p_phenotypeArray[$i][0] = $current_trait[0];
					$p_phenotypeArray[$i][1] = $current_trait[0];
					$p_phenotypeArray[$i][2] = $current_trait[1];
					$p_phenotypeArray[$i][3] = $current_trait[2];
				}
				else if (strcmp($epistasisRatio,$g_epistaticRatios[4]) == 0)
				{
					// a ratio of 15:1
					$p_phenotypeArray[$i][0] = $current_trait[0];
					$p_phenotypeArray[$i][1] = $current_trait[0];
					$p_phenotypeArray[$i][2] = $current_trait[0];
					$p_phenotypeArray[$i][3] = $current_trait[1];
				}

			}
		}
		return $p_phenotypeArray;

	}
	/**
	 * updateProgenyGenerated: helper function to update row in Student Problem table
	 */

	function updateProgenyGenerated($p_userId,$p_currProgGenerated)
	{
		global $g_db;
	
		$sqlQuery = "UPDATE `StudentProblem`
						 SET `ModificationDate` = NOW( ) ,
						`ProgenyGenerated` = ". $p_currProgGenerated.
						 " WHERE `UserId` = '". $g_db->sqlString($p_userId) ."'" ;


		//echo "<p> DEBUGGING STATEMENT (UPDATEPROGENY GENERATED FUNCTION)".$sqlQuery;

		return $g_db->queryCommit($sqlQuery);
	}


	/**
	 * assignProblem: inserts a record into StudentProblem table of DB
	 * based on a masterproblem
	 *
	 * PRE: parameters are non-null values
	 *
	 * POST: bool indicating whether the action was succesful or not
	 *
	 * @param string $p_userId
	 * @param int $p_masterProblemId
	 * @return bool
	 */
	function assignProblem($p_userId,$p_masterProblemId)
	{
		global $g_db;

		$recordset = TA::viewProblem($p_masterProblemId);

		$cross = new Cross($this->m_traitOrder, $this->m_traitNames, $this->m_phenoNames);

		if (!empty($recordset))
		{
			$row = $g_db->fetch($recordset);
			$epistasisCode;

			if (isset($row->EpistasisCode))
			{
				$epistasisCode = $row->EpistasisCode;
			}
			else
			{
				$epistasisCode = "NULL";
			}

			

			$record_row_values = "'" . $g_db->sqlString($p_userId) . "'," . $p_masterProblemId . ",0,'". $g_db->sqlString($row->Description) . "','" . $g_db->sqlString($row->Name) . "'," .
								 $row->GMU1_2 . "," . $row->GMU2_3 . ",'" . $g_db->sqlString($row->TraitOrder) . "'," . $epistasisCode . ",'" .
								 $g_db->sqlString($row->Trait1Name) . "','"  . $g_db->sqlString($row->Trait1AAPhenoName) . "','" .
								 $g_db->sqlString($row->Trait1AbPhenoName) . "','" . $g_db->sqlString($row->Trait1bAPhenoName) . "','" . $g_db->sqlString($row->Trait1bbPhenoName) . "','" .
								 $g_db->sqlString($row->Trait2Name) .  "','" . $g_db->sqlString($row->Trait2AAPhenoName) . "','" .
								 $g_db->sqlString($row->Trait2AbPhenoName) . "','" . $g_db->sqlString($row->Trait2bAPhenoName) . "','" . $g_db->sqlString($row->Trait2bbPhenoName) . "','" .
								 $g_db->sqlString($row->Trait3Name) .  "','" . $g_db->sqlString($row->Trait3AAPhenoName) . "','" .
								 $g_db->sqlString($row->Trait3AbPhenoName) . "','" . $g_db->sqlString($row->Trait3bAPhenoName) . "','" . $g_db->sqlString($row->Trait3bbPhenoName) . "'," .
								 $row->ProgenyPerMating . "," . $row->MaxProgeny . " ";

			$sql_query = 	"INSERT ".
							"INTO StudentProblem(UserId,MasterProblemId,Modified,Description,Name,GMU1_2,GMU2_3,TraitOrder,EpistasisCode,".
							"Trait1Name,Trait1AAPhenoName,Trait1AbPhenoName,Trait1bAPhenoName,Trait1bbPhenoName,".
							"Trait2Name,Trait2AAPhenoName,Trait2AbPhenoName,Trait2bAPhenoName,Trait2bbPhenoName,".
							"Trait3Name,Trait3AAPhenoName,Trait3AbPhenoName,Trait3bAPhenoName,Trait3bbPhenoName,".
						 	"ProgenyPerMating,MaxProgeny) ".
						 	"VALUES (" . $record_row_values . ")";

//echo "SQL query is <br>" . $sql_query . "<p>";

			if ($g_db->queryCommit($sql_query)!=true)
			{
				(new UserError) -> addError(605);
				return false;
			}

			// now, we have to issue a cross
			$db_success = false;
			if (!empty($row->EpistasisCode))
			{
				// $db_success = Cross::generateProgeny($p_userId, 0, 1,'1111', 0, 1, '1111',
			    //                    	   $row->GMU1_2, $row->GMU2_3, $row->ProgenyPerMating, 1);
				$db_success = $cross -> generateProgeny($p_userId, 0, 1,'1111', 0, 1, '1111',
			                       	   $row->GMU1_2, $row->GMU2_3, $row->ProgenyPerMating, 1);
			}
			else
			{
				// $db_success = Cross::generateProgeny($p_userId, 0, 1,'111 ', 0, 1, '111 ',
			    //                    	   $row->GMU1_2, $row->GMU2_3, $row->ProgenyPerMating, 1);
				$db_success = $cross -> generateProgeny($p_userId, 0, 1,'111 ', 0, 1, '111 ',
			                       	   $row->GMU1_2, $row->GMU2_3, $row->ProgenyPerMating, 1);
			}

			if ($db_success)
			{
				//update the progenyGenerated field in StudentProblem because Cross::generateProgeny was called directly
				if (TA::updateProgenyGenerated($p_userId,$row->ProgenyPerMating))
				{
					return true;
				}
					return false;
			}
			else
			{
				//generateProgeny failed, so return false
				return false;
			}
		}
		else
		{
			(new UserError) -> addError(653);
			return false;
		}

		return true;
	}

	/**
	 * reassignProblem: updates a record of StudentProblem table of DB
	 * based on a masterproblem
	 *
	 * PRE: parameters are non-null values
	 *
	 * POST: bool indicating whether the action was succesful or not
	 *
	 * @param string $p_userId
	 * @param int $p_masterProblemId
	 * @return bool
	 */
	function reassignProblem($p_userId,$p_masterProblemId)
	{
		global $g_db;

		$sql_query;
		
		$cross = new Cross($this->m_traitOrder, $this->m_traitNames, $this->m_phenoNames);

		$recordset = TA::viewProblem($p_masterProblemId);

		if (!empty($recordset))
		{
			$row = $g_db->fetch($recordset);
			$epistasisCode;

			if (isset($row->EpistasisCode))
			{
				$epistasisCode = $row->EpistasisCode;
			}
			else
			{
				$epistasisCode = "NULL";
			}
			$sql_query = 	"UPDATE StudentProblem ".
							"SET MasterProblemId = " . $p_masterProblemId . ",".
							"	 Modified = 0," .
							"	 Description = '". $g_db->sqlString($row->Description) . "'," .
							"	 Name = '". $g_db->sqlString($row->Name) . "'," .
							"	 GMU1_2 = " . $row->GMU1_2 . "," .
							"	 GMU2_3 = " . $row->GMU2_3 . "," .
							"	 TraitOrder = '" . $row->TraitOrder . "'," .
							"	 EpistasisCode = " . $epistasisCode . "," .
							"	 Trait1Name = '" . $g_db->sqlString($row->Trait1Name) . "'," .
							"	 Trait1AAPhenoName = '" . $g_db->sqlString($row->Trait1AAPhenoName) . "'," .
							"	 Trait1AbPhenoName = '" . $g_db->sqlString($row->Trait1AbPhenoName) . "'," .
							"	 Trait1bAPhenoName = '" . $g_db->sqlString($row->Trait1bAPhenoName) . "'," .
							"	 Trait1bbPhenoName = '" . $g_db->sqlString($row->Trait1bbPhenoName) . "'," .
							"	 Trait2Name = '" . $g_db->sqlString($row->Trait2Name) . "'," .
							"	 Trait2AAPhenoName = '" . $g_db->sqlString($row->Trait2AAPhenoName) . "'," .
							"	 Trait2AbPhenoName = '" . $g_db->sqlString($row->Trait2AbPhenoName) . "'," .
							"	 Trait2bAPhenoName = '" . $g_db->sqlString($row->Trait2bAPhenoName) . "'," .
							"	 Trait2bbPhenoName = '" . $g_db->sqlString($row->Trait2bbPhenoName) . "'," .
							"	 Trait3Name = '" . $g_db->sqlString($row->Trait3Name) . "'," .
							"	 Trait3AAPhenoName = '" . $g_db->sqlString($row->Trait3AAPhenoName) . "'," .
							"	 Trait3AbPhenoName = '" . $g_db->sqlString($row->Trait3AbPhenoName) . "'," .
							"	 Trait3bAPhenoName = '" . $g_db->sqlString($row->Trait3bAPhenoName) . "'," .
							"	 Trait3bbPhenoName = '" . $g_db->sqlString($row->Trait3bbPhenoName) . "'," .
							"	 ProgenyPerMating = " . $row->ProgenyPerMating . "," .
							" 	 MaxProgeny = ". $row->MaxProgeny .
							"		WHERE UserId = '" . $g_db->sqlString($p_userId) . "'";

			//echo "SQL QUERY..<br>" . $sql_query . "<p>";

			if ($g_db->queryCommit($sql_query)!=true)
			{
				(new UserError) -> addError(606);
				return false;
			}

			// remove all the current DB entries in the Cross and LongerGeneSequences
			// table
			if (TA::deleteCrossSimulation($p_userId) != true)
			{
				return false;
			}

			// now, we have to issue a cross
			$db_success = false;
			if (!empty($row->EpistasisCode))
			{
				// $db_success = Cross::generateProgeny($p_userId, 0, 1,'1111', 0, 1, '1111',
			    //                    	   $row->GMU1_2, $row->GMU2_3, $row->ProgenyPerMating, 1);

				$db_success = $cross -> generateProgeny($p_userId, 0, 1,'1111', 0, 1, '1111',
			                       	   $row->GMU1_2, $row->GMU2_3, $row->ProgenyPerMating, 1);
			}
			else
			{
				// $db_success = Cross::generateProgeny($p_userId, 0, 1,'111 ', 0, 1, '111 ',
			    //                    	   $row->GMU1_2, $row->GMU2_3, $row->ProgenyPerMating, 1);
				$db_success = $cross -> generateProgeny($p_userId, 0, 1,'111 ', 0, 1, '111 ',
			                       	   $row->GMU1_2, $row->GMU2_3, $row->ProgenyPerMating, 1);
			}

			if ($db_success)
			{
				//update the progenyGenerated field in StudentProblem because Cross::generateProgeny was called directly
				if (TA::updateProgenyGenerated($p_userId,$row->ProgenyPerMating))
				{
					return true;
				}
					return false;
			}
			else
			{
				//generateProgeny failed, so return false
				return false;
			}

		}
		else
		{
			(new UserError) -> addError(653);
			return false;
		}

		return true;
	}

	/**
	 * assignModifiedProblem: inserts a record into StudentProblem table of DB
	 * this function will also invoke the first initial cross
	 *
	 * PRE: parameters are non-null values
	 *
	 * POST: bool indicating whether the action was succesful or not
	 *
	 * @param string $p_userId
	 * @param int $p_masterProblemId
	 * @param string $p_description
	 * @param string $p_name
	 * @param float $p_gmu1_2
	 * @param float $p_gmu2_3
	 * @param string $p_traitOrder
	 * @param int $p_epistatisCode
	 * @param string [] $p_arrPhenotypeNames (such as ["Height","Color"])
	 * @param string [][] $p_arrPhenotypes(such as [ 0 => ["Tall","Short"],
	 *												 1 => ["Yellow","Red"] ] )
	 * @param int $p_progenyPerMating
	 * @param int $p_maxProgeny
	 * @return bool
	 */
	function assignModifiedProblem($p_userId, $p_masterProblemId, $p_description, $p_name, $p_gmu1_2, $p_gmu2_3, $p_traitOrder, $p_epistasisCode, $p_arrPhenotypeNames, $p_arrPhenotypes, $p_progenyPerMating, $p_maxProgeny)
	{
		global $g_db;

		//check the progeny values
		if (! TA::validProgenyValues($p_progenyPerMating, $p_maxProgeny)) {
			return false;
		}

		// determine the traitnames
		$p_trait1Name = $p_arrPhenotypeNames[0];
		$p_trait2Name = $p_arrPhenotypeNames[1];
		$p_trait3Name = $p_arrPhenotypeNames[2];

		$phenotypeArray = array();
		//TA::assignPhenotypeLogic($p_epistasisCode, $p_arrPhenotypes, &$phenotypeArray);

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
			$record_row_values = "'" . $g_db->sqlString($p_userId) . "'," . $p_masterProblemId . ",1,'" . $g_db->sqlString($p_description) . "','" . $g_db->sqlString($p_name) . "'," .
								 $p_gmu1_2 . "," . $p_gmu2_3 . ",'" . $g_db->sqlString($p_traitOrder) . "'," .
								 $p_epistasisCode . ",'" .
								 $g_db->sqlString($p_trait1Name) . "','"  . $g_db->sqlString($p_trait1AAName) . "','" .
								 $g_db->sqlString($p_trait1AbName) . "','" . $g_db->sqlString($p_trait1bAName) . "','" . $g_db->sqlString($p_trait1bbName) . "','" .
								 $g_db->sqlString($p_trait2Name) .  "','" . $g_db->sqlString($p_trait2AAName) . "','" .
								 $g_db->sqlString($p_trait2AbName) . "','" . $g_db->sqlString($p_trait2bAName) . "','" . $g_db->sqlString($p_trait2bbName) . "','" .
								 $g_db->sqlString($p_trait3Name) .  "','" . $g_db->sqlString($p_trait3AAName) . "','" .
								 $g_db->sqlString($p_trait3AbName) . "','" . $g_db->sqlString($p_trait3bAName) . "','" . $g_db->sqlString($p_trait3bbName) . "'," .
								 $p_progenyPerMating . "," . $p_maxProgeny . " ";


			$sql_query = 	"INSERT ".
							"INTO StudentProblem(UserId,MasterProblemId,Modified,Description,Name,GMU1_2,GMU2_3,TraitOrder,EpistasisCode,".
							"Trait1Name,Trait1AAPhenoName,Trait1AbPhenoName,Trait1bAPhenoName,Trait1bbPhenoName,".
							"Trait2Name,Trait2AAPhenoName,Trait2AbPhenoName,Trait2bAPhenoName,Trait2bbPhenoName,".
							"Trait3Name,Trait3AAPhenoName,Trait3AbPhenoName,Trait3bAPhenoName,Trait3bbPhenoName,".
						 	"ProgenyPerMating,MaxProgeny) ".
						 	"VALUES (" . $record_row_values . ")";
		}
		else
		{
			$record_row_values = "'" . $g_db->sqlString($p_userId) . "'," . $p_masterProblemId . ",1,'" . $g_db->sqlString($p_description) . "','" . $g_db->sqlString($p_name) . "'," .
								 $p_gmu1_2 . "," . $p_gmu2_3 . ",'" . $g_db->sqlString($p_traitOrder) . "','" .
								 $g_db->sqlString($p_trait1Name) . "','"  . $g_db->sqlString($p_trait1AAName) . "','" .
								 $g_db->sqlString($p_trait1AbName) . "','" . $g_db->sqlString($p_trait1bAName) . "','" . $g_db->sqlString($p_trait1bbName) . "','" .
								 $g_db->sqlString($p_trait2Name) .  "','" . $g_db->sqlString($p_trait2AAName) . "','" .
								 $g_db->sqlString($p_trait2AbName) . "','" . $g_db->sqlString($p_trait2bAName) . "','" . $g_db->sqlString($p_trait2bbName) . "','" .
								 $g_db->sqlString($p_trait3Name) .  "','" . $g_db->sqlString($p_trait3AAName) . "','" .
								 $g_db->sqlString($p_trait3AbName) . "','" . $g_db->sqlString($p_trait3bAName) . "','" . $g_db->sqlString($p_trait3bbName) . "'," .
								 $p_progenyPerMating . "," . $p_maxProgeny . " ";

			$sql_query = 	"INSERT ".
							"INTO StudentProblem(UserId,MasterProblemId,Modified,Description,Name,GMU1_2,GMU2_3,TraitOrder,".
							"Trait1Name,Trait1AAPhenoName,Trait1AbPhenoName,Trait1bAPhenoName,Trait1bbPhenoName,".
							"Trait2Name,Trait2AAPhenoName,Trait2AbPhenoName,Trait2bAPhenoName,Trait2bbPhenoName,".
							"Trait3Name,Trait3AAPhenoName,Trait3AbPhenoName,Trait3bAPhenoName,Trait3bbPhenoName,".
						 	"ProgenyPerMating,MaxProgeny) ".
						 	"VALUES (" . $record_row_values . ")";
		}

		/*echo "Record values..<br>";
		echo $record_row_values;
		echo "<p>";
		echo "SQL query...<br>";
		echo $sql_query;*/

		if ($g_db->queryCommit($sql_query)!=true)
		{
			(new UserError) -> addError(605);
			return false;
		}

		// now, we have to issue a cross
		$db_success = false;
		if ($p_epistasisCode != -1)
		{
			$db_success = Cross::generateProgeny($p_userId, 0, 1,'1111', 0, 1, '1111',
		                       	   $p_gmu1_2, $p_gmu2_3, $p_progenyPerMating, 1);
		}
		else
		{
			$db_success = Cross::generateProgeny($p_userId, 0, 1,'111 ', 0, 1, '111 ',
		                       	   $p_gmu1_2, $p_gmu2_3, $p_progenyPerMating, 1);
		}

		if ($db_success)
			{
				//update the progenyGenerated field in StudentProblem because Cross::generateProgeny was called directly
				if (TA::updateProgenyGenerated($p_userId,$p_progenyPerMating))
				{
					return true;
				}
					return false;
			}
			else
			{
				//generateProgeny failed, so return false
				return false;
			}

		return true;

	}

	/**
	 * reassignModifiedProblem: inserts a record into StudentProblem table of DB
	 * this function will also invoke the first initial cross
	 *
	 * PRE: parameters are non-null values
	 *
	 * POST: bool indicating whether the action was succesful or not
	 *
	 * @param string $p_userId
	 * @param int $p_masterProblemId
	 * @param string $p_description
	 * @param string $p_name
	 * @param float $p_gmu1_2
	 * @param float $p_gmu2_3
	 * @param string $p_traitOrder
	 * @param int $p_epistatisCode
	 * @param string [] $p_arrPhenotypeNames (such as ["Height","Color"])
	 * @param string [][] $p_arrPhenotypes(such as [ 0 => ["Tall","Short"],
	 *												 1 => ["Yellow","Red"] ] )
	 * @param int $p_progenyPerMating
	 * @param int $p_maxProgeny
	 * @return bool
	 */
	function reassignModifiedProblem($p_userId, $p_masterProblemId,$p_description, $p_name, $p_gmu1_2, $p_gmu2_3, $p_traitOrder, $p_epistasisCode, $p_arrPhenotypeNames, $p_arrPhenotypes, $p_progenyPerMating, $p_maxProgeny)
	{
		global $g_db;

		//check the progeny values
		if (! TA::validProgenyValues($p_progenyPerMating, $p_maxProgeny)) {
			return false;
		}

		// determine the traitnames
		$p_trait1Name = $p_arrPhenotypeNames[0];
		$p_trait2Name = $p_arrPhenotypeNames[1];
		$p_trait3Name = $p_arrPhenotypeNames[2];

		$phenotypeArray = array();
		//TA::assignPhenotypeLogic($p_epistasisCode, $p_arrPhenotypes, &$phenotypeArray);
		//$phenotypeArray = TA::assignPhenotypeLogic($p_epistasisCode, $p_arrPhenotypes, &$phenotypeArray);
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
		if ($p_epistasisCode != -1)
		{
			$sql_query = 	"UPDATE StudentProblem ".
							"SET MasterProblemId = " . $p_masterProblemId . ",".
							"	 Modified = 1," .
							"	 Description = '". $g_db->sqlString($p_description) . "'," .
							"	 Name = '". $g_db->sqlString($p_name) . "'," .
							"	 GMU1_2 = " . $p_gmu1_2 . "," .
							"	 GMU2_3 = " . $p_gmu2_3 . "," .
							"	 TraitOrder = '" . $p_traitOrder . "'," .
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
							"		WHERE UserId = '" . $g_db->sqlString($p_userId) . "'";
		}
		else
		{
			$sql_query = 	"UPDATE StudentProblem ".
							"SET MasterProblemId = " . $p_masterProblemId . ",".
							"	 Modified = 1," .
							"	 Description = '". $g_db->sqlString($p_description) . "'," .
							"	 Name = '". $g_db->sqlString($p_name) . "'," .
							"	 GMU1_2 = " . $p_gmu1_2 . "," .
							"	 GMU2_3 = " . $p_gmu2_3 . "," .
							"	 TraitOrder = '" . $p_traitOrder . "'," .
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
							"		WHERE UserId = '" . $g_db->sqlString($p_userId) . "'";
		}

		if ($g_db->queryCommit($sql_query)!=true)
		{
			(new UserError) -> addError(606);
			return false;
		}

		//echo $sql_query;

		// remove all the current DB entries in the Cross and LongerGeneSequences
		// table
		if (TA::deleteCrossSimulation($p_userId) != true)
		{
			return false;
		}

		// now, we have to issue a cross
		$db_success = false;
		if ($p_epistasisCode != -1)
		{
			$db_success = Cross::generateProgeny($p_userId, 0, 1,'1111', 0, 1, '1111',
		                       	   $p_gmu1_2, $p_gmu2_3, $p_progenyPerMating, 1);
		}
		else
		{
			$db_success = Cross::generateProgeny($p_userId, 0, 1,'111 ', 0, 1, '111 ',
		                       	   $p_gmu1_2, $p_gmu2_3, $p_progenyPerMating, 1);
		}

			if ($db_success)
			{
				//update the progenyGenerated field in StudentProblem because Cross::generateProgeny was called directly
				if (TA::updateProgenyGenerated($p_userId,$p_progenyPerMating))
				{
					return true;
				}
					return false;
			}
			else
			{
				//generateProgeny failed, so return false
				return false;
			}

		return true;
	}


	/**
	 * getStudents: this will get all the students belonging to the same course as the TA
	 * PRE: none
	 *
	 * POST: a recordset of students from User table
	 *
	 * @return recordset
	 */
	function getStudents()
	{
		global $g_db;

		$default_courseId = $this->m_courseId;
		$sql_query = "SELECT a.UserId, a.CourseId, a.PrivilegeLvl, a.FirstName, a.LastName, b.Name, b.CourseId
				FROM User a
				LEFT JOIN StudentProblem b ON (a.UserId=b.UserId
				AND b.CourseId='%$default_courseId%')
				WHERE a.CourseId LIKE '%$default_courseId%'
				AND PrivilegeLvl LIKE '%3%' 
				ORDER BY UserId";
	
		// var_dump($sql_query);
		return $g_db->querySelect($sql_query);
	}

	/**
	 * getStudent: this will get one student from the User table
	 * PRE: $p_userId is a non-null value
	 *
	 * POST: a recordset of a student with $p_userId
	 *
	 * @return recordset
	 */
	function getStudent($p_userId)
	{
		global $g_db;
		$sql_query = 	"SELECT UserId,CourseId,PrivilegeLvl,FirstName,LastName".
		 				" FROM User ".
		 				"	WHERE UserId = '" . $g_db->sqlString($p_userId) . "' AND PrivilegeLvl = 3";

		return $g_db->querySelect($sql_query);
	}

	/**
	 * getStudentProblem: this will get a problem from the StudentProblem DB Table
	 *
	 * PRE: $p_userId is non-null value
	 *
	 * POST: a recordset from StudentProblem table associated with $p_userId
	 *
	 * @param string $p_userId
	 * @return recordset
	 */
	function getStudentProblem($p_userId)
	{
		global $g_db;

		$sql_query = "SELECT MasterProblemId,Modified,Description,Name,GMU1_2,GMU2_3,TraitOrder,UNIX_TIMESTAMP(ModificationDate) AS FormattedTime,EpistasisCode,".
					 "Trait1Name,Trait1AAPhenoName,Trait1AbPhenoName,Trait1bAPhenoName,Trait1bbPhenoName,".
					 "Trait2Name,Trait2AAPhenoName,Trait2AbPhenoName,Trait2bAPhenoName,Trait2bbPhenoName,".
					 "Trait3Name,Trait3AAPhenoName,Trait3AbPhenoName,Trait3bAPhenoName,Trait3bbPhenoName,".
					 "ProgenyPerMating,MaxProgeny ".
					 "FROM StudentProblem ".
						"	WHERE UserId = '" . $g_db->sqlString($p_userId) . "'";

		return $g_db->querySelect($sql_query);
	}

	/**
	 * getStudentProblems: this will get a list of problems from the StudentProblem DB Table
	 *
	 * PRE: $p_masterProblemId is non-null value
	 *
	 * POST: a recordset of problems StudentProblem table associated that contains
	 * a link to $p_masterProblemId
	 *
	 * @param int $p_masterProblemId
	 * @return recordset
	 */
	function getStudentProblems($p_masterProblemId)
	{
		global $g_db;
		$sql_query = "SELECT UserId,MasterProblemId,Modified,Description,Name,GMU1_2,GMU2_3,TraitOrder,UNIX_TIMESTAMP(ModificationDate) AS FormattedTime,EpistasisCode,".
					 "Trait1Name,Trait1AAPhenoName,Trait1AbPhenoName,Trait1bAPhenoName,Trait1bbPhenoName,".
					 "Trait2Name,Trait2AAPhenoName,Trait2AbPhenoName,Trait2bAPhenoName,Trait2bbPhenoName,".
					 "Trait3Name,Trait3AAPhenoName,Trait3AbPhenoName,Trait3bAPhenoName,Trait3bbPhenoName,".
					 "ProgenyPerMating,MaxProgeny ".
					 "FROM StudentProblem ".
					 "	WHERE MasterProblemId = " . $p_masterProblemId .
					 " ORDER BY UserId";

		return $g_db->querySelect($sql_query);
	}


	/**
	 * getProblems: this will get all the problems belonging to the same course as the TA
	 *
	 * PRE: none
	 *
	 * POST: a recordset of problems from MasterProblem table
	 *
	 * @return recordset
	 */
	function getProblems()
	{
		global $g_db;
		$default_courseId = $this->m_courseId;
		//$default_courseId = 4;

		$sql_query = "SELECT ProblemId,Description,Name,GMU1_2,GMU2_3,TraitOrder,UNIX_TIMESTAMP(ModificationDate) AS FormattedTime,CourseId,EpistasisCode,".
					 "Trait1Name,Trait1AAPhenoName,Trait1AbPhenoName,Trait1bAPhenoName,Trait1bbPhenoName,".
					 "Trait2Name,Trait2AAPhenoName,Trait2AbPhenoName,Trait2bAPhenoName,Trait2bbPhenoName,".
					 "Trait3Name,Trait3AAPhenoName,Trait3AbPhenoName,Trait3bAPhenoName,Trait3bbPhenoName,".
					 "ProgenyPerMating,MaxProgeny ".
					 "FROM MasterProblem ".
					 "	WHERE CourseId = ". $default_courseId .
					 "  ORDER BY Name";;

		return $g_db->querySelect($sql_query);
	}

	/**
	 * imports a student list
	 *
	 * PRE: $p_file is a valid file
	 *
	 * POST: bool indicating whether the import was successful or not
	 *
	 * @return bool
	 */
	function importStudents($p_lineArray, $p_studentListArray, $p_studentErrorListArray)
	{
		// $p_studentListArray will be in the form of
		// array ( 0 => ["userId","first name","last name"],
		// where bool will indicate if the parsed student record
		// had any errors

		/*echo "Here are the lines of the given file:<p>";
		for ($tempCounter = 0; $tempCounter < count($p_lineArray); $tempCounter++)
		{
			echo $p_lineArray[$tempCounter] . "<br>";
		}
		echo "<p>";*/

		//$fhandle=fopen($p_file,"r");
		//while (!feof($fhandle))
		$arrayOfResult = [[], []];
		if (isset($p_lineArray)) {
			for ($tempCounter = 0; $tempCounter < count($p_lineArray); $tempCounter++)
			{
   				//$line = fgets($fhandle);
   				$line = $p_lineArray[$tempCounter];
   				list($userId, $firstName,$lastName) = explode(",",$line);

				// check for bad values
				if (empty($userId ) || empty($firstName) || empty($lastName))
				{
					$arrayOfResult[1][] = array($userId,$firstName,$lastName);
					// $p_studentErrorListArray[] = array($userId,$firstName,$lastName);
				}
				else
				{
					$arrayOfResult[0][] = array($userId, $firstName, $lastName);
					// $p_studentListArray[] = array($userId,$firstName,$lastName);
				}
			}
		}
		return $arrayOfResult;
   		//fclose($fhandle);
	}

	/**
	 * getPhenotypes: this will get all the phenotypes associated with a traitId
	 *
	 * PRE: $traitId is a non-null value
	 *
	 * POST: a recordset of phenotypes from Phenotype table
	 *
	 * @return recordset
	 */
	function getPhenotypes($p_traitId)
	{
		global $g_db;

		$sql_query = "SELECT PhenotypeId,TraitId,Name ".
					 "FROM Phenotype ".
					 "WHERE TraitId=". $p_traitId.
					 " ORDER BY Name";

		return $g_db->querySelect($sql_query);
	}

	/**
	 * getTraits: this will get all the traits associated with the user's courseId
	 *
	 * PRE: none
	 *
	 * POST: a recordset of traits from Trait table
	 *
	 * @return recordset
	 */
	function getTraits()
	{
		global $g_db;
		$default_courseId = $this->m_courseId;

		$sql_query = "SELECT TraitId,CourseId,Name ".
					 "FROM Trait ".
					 "WHERE CourseId=". $default_courseId .
					 " ORDER BY Name";

		return $g_db->querySelect($sql_query);
	}

	/**
	 * deleteCrossSimulation: a helper function to remove all DB entries in the
	 * Cross and LongerGeneSequences table corresponding to a student
	 *
	 * PRE: $p_userId are non-null values
	 *
	 * POST: all entries in the Cross and LongerGeneSequences table corresponding
	 * to $p_userId are removed
	 *
	 * @param string $p_userId
	 * @return bool result
	 */
	function deleteCrossSimulation($p_userId)
	{
		global $g_db;

		// ok, now we've also got to delete any entry with this userId in the
		// Cross table
		// let's check to see if an entry even exist first
		$sql_query = 	"SELECT UserId ".
						"FROM `Cross` ".
						" 	WHERE UserId = '". $g_db->sqlString($p_userId) . "'";

		$recordset = $g_db->querySelect($sql_query);
		if (!empty($recordset))
		{
			$sql_query = 	"DELETE ".
							"FROM `Cross` ".
							"	WHERE UserId = '". $g_db->sqlString($p_userId) . "'";

			if ($g_db->queryCommit($sql_query)!= true)
			{
				(new UserError) -> addError(610);
				return false;
			}
		}

		// ok, now we've also got to delete any entry with this userId in the
		// LongerGeneSequences table
		// let's check to see if an entry even exist first

		$sql_query = 	"SELECT UserId ".
						"FROM LongerGeneSequences ".
						"	WHERE UserId = '". $g_db->sqlString($p_userId) . "'";

		$recordset = $g_db->querySelect($sql_query);
		if (!empty($recordset))
		{
			$sql_query = 	"DELETE ".
							"FROM LongerGeneSequences ".
							"	WHERE UserId = '". $g_db->sqlString($p_userId) . "'";

			if ($g_db->queryCommit($sql_query)!= true)
			{
				(new UserError) -> addError(610);
				return false;
			}
		}
		return true;
	}

	function importClassList($payload) {
		$result = [];
		$cn = self::getCommonName($payload);

		$ds = ldap_connect(LDAP_HOST);
		ldap_set_option($ds, LDAP_OPT_NETWORK_TIMEOUT, 3);

		ldap_start_tls($ds);

		if ($ds) {
			// remove warning when bind fails
			set_error_handler(function() {});
			$r=ldap_bind($ds, LDAP_DN, LDAP_PW);
			restore_error_handler();


			// var_dump($ds, LDAP_DN, LDAP_PW);
			// var_dump($r);

			if ($r) {
				$base_dn = "ou=UBC,ou=ACADEMIC,dc=id,dc=ubc,dc=ca";
				$filter = "(&(objectClass=*)(cn=".$cn."))";

				$sr=ldap_search($ds, $base_dn, $filter);
				$info = ldap_get_entries($ds, $sr);
				$uniquemember = $info[0]['uniquemember'];
				for ($i = 0; $i < $uniquemember['count']; $i++) {
					$temp = substr($uniquemember[$i], 4);
					$temp = explode(",", $temp)[0];
					array_push($result, $temp);
				}
			} else {
				$result = null;
				echo "<h2 style=\"color:red;\"> Failed to retrieve student records. Please make sure you are connected to UBC VPN</h2>";
			};
		}
		ldap_close($ds);
		return $result;
	}

	function getCommonName($payload) {
		$result = "";
		$result = $payload['subjectCode']."_";
		$result = $result.$payload['courseNumber']."_";
		$result = $result.$payload['section']."_";
		$result = $result.$payload['year'].$payload['session'];
		return $result;
	}

	function deleteAllStudents(){
		global $g_db;
		$students = self::getStudents();

		while ($row = $g_db->fetch($students)) {
			self::deleteStudent($row->UserId);
		}
	}

}
