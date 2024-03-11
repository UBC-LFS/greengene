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
				WHERE UserId='".$g_db -> sqlString($p_userId)."'";
		$rs = $g_db -> querySelect($SQL);


		if ($g_db -> getNumRows($rs) == 1)
		{

			// if (self::ldap_login($p_userId, $p_pwd)) {
			if (true) { // does not authenciate password - testing purposes

				$row = $g_db -> fetch($rs);

				// defaults to first privilege level
				$row -> PrivilegeLvl = explode(',', $row -> PrivilegeLvl)[0];

				// var_dump($row->PrivilegeLvl);
				// $row -> PrivilegeLvl = 1; // TESTING
				switch ($row -> PrivilegeLvl){
					case 10:
					// if MasterAdmin is acting like a Administrator, give it the Administrator role
					
					// if (str_contains($_SERVER['PHP_SELF'], '/admin')) {
					// 	$user = new Administrator($p_userId);
					// } else {
					$user = new MasterAdmin($p_userId);
					// }
					
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
		}
		return false;
	}

	function ldap_login($p_userId, $p_pwd) {
		$ldap = LDAP_LOGIN_HOST;
		// TODO: change dn - path to students cwl user
		$usr = "uid=".$p_userId.",ou=People,dc=landfood,dc=ubc,dc=ca";
		$ds = ldap_connect($ldap);
		ldap_set_option($ds, LDAP_OPT_NETWORK_TIMEOUT, 3);
		if ($ds) {
			set_error_handler(function(){});
			ldap_start_tls($ds);
			$result = ldap_bind($ds, $usr, $p_pwd);
			restore_error_handler();
			return $result;
		}
		return false;
	}

	/**
	 * getUser: Login method will take in user name and password check account existance and return user object.
	 * PRE: a valid user session data
	 * POST: return false if session does not exist, return user if session does exist
	 * @return user
	 */
	function getUser($p_checkSession=true)
	{
		if ($p_checkSession && isset($_SESSION['userSession']))
		{
			// // let master admin act as a prof
			// if ($_SESSION['userSession']->m_privilegeLvl == 10 And str_contains($_SERVER['PHP_SELF'], '/admin') And (!str_contains($_SERVER['PHP_SELF'], 'selectcourse'))) {
			// 	// var_dump("testing");
			// 	var_dump($_SESSION['userSession']->m_userId);

			// 	var_dump("error in security.php getUser");

			// 	$user = new Administrator($_SESSION['userSession']->m_userId);
			// 	$_SESSION['userSession'] = $user;
				
			// 	var_dump("does return break it?");

			// 	return $user;
			// }

			return $_SESSION['userSession'];
		}
		return false;
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
