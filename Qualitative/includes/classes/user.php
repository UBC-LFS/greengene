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
		$sqlQuery = "SELECT FirstName,LastName,PrivilegeLvl,CourseId, Pwd
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

	/**
	 * Change (current) user's password
	 *
	 * @param string @p_oldPwd current password
	 * @param string @p_pwd1 new password (first emtry)
	 * @param string @p_pwd2 new password (second entry)
	 * @return bool
	 */
	function changePassword($p_oldPwd, $p_pwd1, $p_pwd2)
	{
		global $g_db;

		$userId = $g_db->sqlString($this->m_userId);
		$pwd = $g_db->sqlString($p_oldPwd);

		$sqlQuery = "SELECT UserId
				FROM User
				WHERE Pwd=PASSWORD('$pwd')
				AND UserId='$userId'";

		$recordSet = $g_db->querySelect($sqlQuery);

		if(strlen($p_pwd1) < 3)
		{
			UserError::addError(301);
			return false;
		}

		//check that the old password matches and that the new passwords match, too
		if(($g_db->getNumRows($recordSet) == 1) && (strcmp($p_pwd1,$p_pwd2) == 0))
		{
			//the old password matches the current pwd and the new pwds match, too, so perform the update
			$sqlQuery = "UPDATE User
					SET Pwd=PASSWORD('".$g_db->sqlString($p_pwd1)."')
					WHERE UserId='$userId'";

			return $g_db->queryCommit($sqlQuery);
		}
		else
		{
			UserError::addError(300);
			return false;
		}

		return true;
	}
}
?>
