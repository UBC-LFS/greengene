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
	function User($p_userId)
	{
		global $g_db;

		// Get user information
		$sqlQuery = "SELECT FirstName,LastName,PrivilegeLvl,CourseId
				FROM User
				WHERE UserId = '".$g_db->sqlString($p_userId)."'";	

		$recordset = $g_db->querySelect($sqlQuery);

		$row = $g_db->fetch($recordset);

		$this->m_userId = $p_userId;
		$this->m_firstName = $row->FirstName;
		$this->m_lastName =  $row->LastName;
		$this->m_privilegeLvl = $row->PrivilegeLvl;
		$this->m_courseId = $row->CourseId;

		// Get course name/description
		$sqlQuery = "SELECT Name, Description
				FROM Course
				WHERE CourseId = '".$g_db->sqlString($this->m_courseId)."'";
		$recordset = $g_db->querySelect($sqlQuery);

		$row = $g_db->fetch($recordset);
		$this->m_courseName = $row->Name;
		$this->m_courseDescription = $row->Description;
	}


}
?>
