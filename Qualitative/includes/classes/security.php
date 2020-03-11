<?php
/**
 * Security class
 *
 * @author Stanley Tso
 * @package Security
 */
if(defined('SESSIONPATH'))
	session_save_path(SESSIONPATH);
session_start();

class Security
{
	/**
	 * login: Login method will take in user name and password check account existance and return user object.
	 * PRE: none
	 * POST: return false if account does not exist, return user if account does exist
	 * @param int $p_userId
	 * @param int $p_pwd
	 * @return user
	 * @return false
	 */
	function login($p_userId, $p_pwd)
	{
		global $g_db;
		$SQL = "SELECT PrivilegeLvl
				FROM User
				WHERE UserId='".$g_db -> sqlString($p_userId)."'
					AND Pwd=Password('". $g_db -> sqlString($p_pwd)."')";
		$rs = $g_db -> querySelect($SQL);
		if ($g_db -> getNumRows($rs) == 1)
		{
			$row = $g_db -> fetch($rs);
			switch ($row -> PrivilegeLvl){
				case 10:
				$user = new MasterAdmin($p_userId);
				break;
				case 1:
				$user = new Administrator($p_userId);
				break;
				case 2:
				$user = new TA($p_userId);
				break;
				case 3:
				$user = new Student($p_userId);
				break;
				default:
				echo "Unknown user privilege level.";
				exit;
			}
			$_SESSION['userSession'] = $user;
			//create session variable
			return $user;
		}
		else
		{
			return false;
		}
	}

	/**
	 * getUser: Login method will take in user name and password check account existance and return user object.
	 * PRE: a valid user session data
	 * POST: return false if session does not exist, return user if session does exist
	 * @return user
	 */
	function getUser($p_checkSession='true')
	{
		if($p_checkSession == false && !isset($_SESSION['userSession']))
		{
			if($_SERVER['PHP_SELF'] != URLROOT.'/login.php') 
			{
				Page::redirect(URLROOT . '/login.php');
			}
		} 
		else 
		{
			return $_SESSION['userSession'];
		}
			
	}

	/**
	 * logout: This logout method will clear session data.
	 * PRE: a valid user session data
	 * POST: session data cleared
	 */
	function logout()
	{
		session_destroy();
	}
}
?>
