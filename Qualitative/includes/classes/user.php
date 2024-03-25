<?php
/**
 * UserError class
 *
 * @package User
 */
class User
{
	var $m_lastName;
	var $m_firstName;
	var $m_userId;
	var $m_privilegeLvl;
	var $m_courseId;
	var $m_courseName;
	var $m_courseDescription;

	/**
	 * Constructor
	 *
	 * Intended to be abstract. This is only meant to be used as a super constructor,
	 * and never called directly.
	 *
	 * @param string $p_userId User Id of user to construct object for
	 */
	// function User($p_userId)
	function __construct($p_userId)
	{
		global $g_db;

		// var_dump($p_userId);

		$courseIndex = NULL; // set default course ID
		// Get the course index from the URL
		if (isset($_GET['course'])) {
			$courseIndex = $_GET['course'];
		}

		// var_dump($courseIndex);

		// var_dump($g_db->sqlString($p_userId));

		// Get user information
		$sqlQuery = "SELECT FirstName,LastName,PrivilegeLvl,CourseId
				FROM User
				WHERE UserId = '".$g_db->sqlString($p_userId)."'";	

		$recordset = $g_db->querySelect($sqlQuery);

		$row = $g_db->fetch($recordset);

		// Convert CourseId & PrivilegeLvl to arrays
		$this->m_courseArray = explode(',', $row->CourseId);
		$this->m_PrivilegeLvlArray = explode(',', $row->PrivilegeLvl);

		$this->m_userId = $p_userId;
		$this->m_firstName = $row->FirstName;
		$this->m_lastName =  $row->LastName;

		// initalized default course
		if ($courseIndex == NULL) {
			$this->m_privilegeLvl = $this->m_PrivilegeLvlArray[0];
			$this->m_courseId = $this->m_courseArray[0];
		} else {
			$this->m_privilegeLvl = $this->m_PrivilegeLvlArray[$courseIndex];
			$this->m_courseId = $this->m_courseArray[$courseIndex];
		}

		// $this->m_privilegeLvl = $row->PrivilegeLvl;
		// $this->m_courseId = $row->CourseId;

		// Get course name/description
		$sqlQuery = "SELECT Name, Description
				FROM Course
				WHERE CourseId = '".$g_db->sqlString($this->m_courseId)."'";
		$recordset = $g_db->querySelect($sqlQuery);

		$row = $g_db->fetch($recordset);

		if ($courseIndex == NULL) {
			$this->m_courseName = "";
			$this->m_courseDescription = "";
		} else {
			$this->m_courseName = $row->Name;
			$this->m_courseDescription = $row->Description;
		}
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

		if ($p_courseId == 0) {
			return false;
		}

		// var_dump("running getCourse");
		// var_dump($p_courseId);

		$result = $g_db->querySelect("SELECT Name, Description
			FROM Course
			WHERE CourseId=$p_courseId");

		if($g_db->getNumRows($result) != 1)
		{
			(new UserError) -> addError(907);
			return false;
		}
		
		$row = $g_db->fetch($result);

		return $row;
	}

}
?>
