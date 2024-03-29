<?php
/**
 * MasterAdmin class
 *
 * @author Brett Taylor
 * @package User
 */
class MasterAdmin extends User
{
	/**
	 * Constructor
	 *
	 * @param string $p_userId UserId
	 */
	// function MasterAdmin($p_userId)
	function __construct($p_userId)
	{
		// parent::User($p_userId); 
		parent::__construct($p_userId);
	}

	/**
	 * Creates a new course and Admin
	 *
	 * @param string $p_courseName course name
	 * @param string $p_description course description
	 * @param string $p_userId admin UserId
	 * @param string $p_firstName admin first name
	 * @param string $p_lastName admin last name
	 * @return bool
	 */
	function createCourse($p_courseName, $p_description, $p_userId,
		$p_firstName, $p_lastName)
	{
		global $g_db;

		if($g_db->queryCommit("INSERT INTO Course " .
			"(Name, Description) " .
			"VALUES('" . $g_db->sqlString($p_courseName) . "', " .
			"'" . $g_db->sqlString($p_description) . "')") != true)
		{
			(new UserError) -> addError(900);
			return false;
		}

		$courseId = $g_db->getLastInsertId();

		if($this->createManagementUser($p_userId, $p_firstName, $p_lastName, $courseId, 1) != true)
		{
			return false;
		}

		return $courseId;
	}

	/**
	 * Deletes specified course
	 *
	 * @param string $p_courseId CourseId
	 * @return bool
	 */
	function deleteCourse($p_courseId)
	{
		global $g_db;

		$g_db->queryCommit("DELETE FROM Course
			WHERE CourseId=$p_courseId");

		$g_db->queryCommit("DELETE FROM MasterProblem
			WHERE CourseId=$p_courseId");

		$users = $g_db->querySelect("SELECT UserId
			FROM User
			WHERE CourseId=$p_courseId");

		while($row = $g_db->fetch($users))
		{
			$userId = $g_db->sqlString($row->UserId);
			$g_db->queryCommit("DELETE FROM StudentProblem
				WHERE UserId='$userId'");
			$g_db->queryCommit("DELETE FROM `Cross`
				WHERE UserId='$userId'");
			$g_db->queryCommit("DELETE FROM LongerGeneSequences
				WHERE UserId='$userId'");
		}

		$traits = $g_db->querySelect("SELECT TraitId
			FROM Trait
			WHERE CourseId=$p_courseId");

		while($row = $g_db->fetch($traits))
			$g_db->queryCommit("DELETE FROM Phenotype
				WHERE TraitId=$row->TraitId");

		$g_db->queryCommit("DELETE FROM Trait
			WHERE CourseId=$p_courseId");

		$g_db->queryCommit("DELETE FROM User
			WHERE CourseId=$p_courseId");

		// finally delete the course
		if($g_db->queryCommit("DELETE FROM Course " .
			"WHERE CourseId=$p_courseId") != true)
		{
			(new UserError) -> addError(903);
			return false;
		}

		return true;
	}

	/**
	 * Modify a course
	 *
	 * @param string $p_courseId course Id
	 * @param string $p_courseName course name
	 * @param string $p_description course description
	 * @return bool
	 */
	function modifyCourse($p_courseId, $p_name, $p_description)
	{
		global $g_db;

		if($g_db->queryCommit("UPDATE Course
			SET Name='" . $g_db->sqlString($p_name) . "',
			Description='" . $g_db->sqlString($p_description) . "'
			WHERE CourseId=$p_courseId") != true)
		{
			(new UserError) -> addError(905);
			return false;
		}

		return true;
	}

	/**
	 * Get all courses
	 *
	 * @return recordset
	 */
	function getCourses()
	{
		global $g_db;

		$result = $g_db->querySelect("SELECT CourseId, Name, Description
			FROM Course
			ORDER BY Name");

		return $result;
	}

	/**
	 * Get course record for specific course
	 *
	 * @param int $p_courseId course Id
	 * @return recordset
	 */
	function getCourse($p_courseId)
	{
		global $g_db;

		$result = $g_db->querySelect("SELECT Name, Description
			FROM Course
			WHERE CourseId=$p_courseId");

		if($g_db->getNumRows($result) != 1)
		{
			(new UserError) -> addError(907);
			return false;
		}

		return $result;
	}

	/**
	 * Creates a new Admin for a course
	 *
	 * @param string $p_userId admin UserId
	 * @param string $p_firstName admin first name
	 * @param string $p_lastName admin last name
	 * @param int $p_courseId course Id
	 * @param int $p_privilegeLvl account privilege level
	 * @return bool
	 */
	function createManagementUser($p_userId, $p_firstName, $p_lastName, $p_courseId, $p_privilegeLvl)
	{
		global $g_db;

		if($p_privilegeLvl != 1 && $p_privilegeLvl != 2)
		{
			(new UserError) -> addError(910);
			return false;
		}

		$result = $g_db->querySelect("SELECT UserId
			FROM User
			WHERE UserId='" . $g_db->sqlString($p_userId) . "'");
		if($g_db->getNumRows($result) != 0)
		{
			// UserError::addError(305);
			(new UserError) -> addError(305);
			return false;
		}

		if($g_db->queryCommit("INSERT INTO User
			(UserId, CourseId, PrivilegeLvl, FirstName, LastName)
			VALUES('" . $g_db->sqlString($p_userId) . "',
			$p_courseId,
			$p_privilegeLvl,
			'" . $g_db->sqlString($p_firstName) . "',
			'" . $g_db->sqlString($p_lastName) . "')") != true)
		{
			// UserError::addError(901);
			(new UserError) -> addError(901);
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
			// UserError::addError(910);
			(new UserError) -> addError(910);
			return false;
		}

		if($g_db->queryCommit("UPDATE User
			SET FirstName='" . $g_db->sqlString($p_firstName) . "',
			LastName='" . $g_db->sqlString($p_lastName) . "',
			PrivilegeLvl=$p_privilegeLvl
			WHERE UserId='" . $g_db->sqlString($p_userId) . "'") != true)
		{
			// UserError::addError(904);
			(new UserError) -> addError(904);
			return false;
		}

		return true;
	}

	/**
	 * Modify MasterAdmin
	 *
	 * @param string $p_userId admin UserId
	 * @param string $p_firstName admin first name
	 * @param string $p_lastName admin last name
	 * @param int $p_privilegeLvl privilege level
	 * @return bool
	 */
	function modifyMasterAdmin($p_userId, $p_firstName, $p_lastName)
	{
		global $g_db;

		if($g_db->queryCommit("UPDATE User
			SET FirstName='" . $g_db->sqlString($p_firstName) . "',
			LastName='" . $g_db->sqlString($p_lastName) . "'
			WHERE PrivilegeLvl=10
			AND UserId='" . $g_db->sqlString($p_userId) . "'") != true)
		{
			// UserError::addError(904);
			(new UserError) -> addError(904);
			return false;
		}

		return true;
	}

	/**
	 * Delete an admin account
	 *
	 * @param string $p_userId admin UserId
	 * @return bool
	 */
	function deleteManagementUser($p_userId)
	{
		global $g_db;

		if($g_db->queryCommit("DELETE FROM User
			WHERE UserId='" . $g_db->sqlString($p_userId) . "'") != true)
		{
			// UserError::addError(909);
			(new UserError) -> addError(909);
			return false;
		}

		return true;
	}

	/**
	 * Get course admins
	 *
	 * @param int $p_courseId course Id
	 * @return recordset
	 */
	function getManagementUsers($p_courseId)
	{
		global $g_db;

		$result = $g_db->querySelect("SELECT UserId, FirstName, LastName, PrivilegeLvl
			FROM User
			WHERE CourseId=$p_courseId
			AND PrivilegeLvl IN (1, 2)
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
			// UserError::addError(906);
			(new UserError) -> addError(906);
			return false;
		}

		return $result;
	}

	/**
	 * Gets user associated to a course
	 * 
	 * @param string $course 
	 * @return recordset
	 */
	function getUsers($course) 
	{
		global $g_db;

		$sql = "SELECT * 
			FROM User
			WHERE CourseId = $course";
		$result = $g_db->querySelect($sql);

		return $result;
	}

	/**
	 * Gets user associated to a course
	 * 
	 * @param string $course 
	 * @return recordset
	 */
	function getProblems($course)
	{
		global $g_db;
		$sql = "SELECT * 
			FROM MasterProblem
			WHERE CourseId = $course";
		$result = $g_db->querySelect($sql);

		return $result;

	}

	function getTraits($course)
	{
		global $g_db;
		$sql = "SELECT *
			FROM Trait
			WHERE CourseId = $course";
		$result = $g_db->querySelect($sql);
		return $result;
	}
}
?>
