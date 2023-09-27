<?php

class LoginManager
{
	function authenticate( $str_username, $str_password, $obj_db )
	{
		$str_sql_query = "SELECT U.UserId, U.PrivilegeLvl, U.FirstName, U.LastName, U.PrivilegeLvl
							          FROM User U
									  WHERE U.UserId = '" . $obj_db->format_sql_string( $str_username ) . "'";

		return LoginManager::query_credentials( $str_sql_query, $obj_db , $str_username, $str_password);
	}
	
	
	function authenticate_hash( $str_username, $str_password_hash, $obj_db )
	{
		$str_sql_query = "SELECT U.UserId, U.PrivilegeLvl, U.FirstName, U.LastName, U.PrivilegeLvl
							          FROM User U
							          WHERE U.UserId = '" . $obj_db->format_sql_string( $str_username ) . "'";
	  
		return LoginManager::query_credentials( $str_sql_query, $obj_db , $str_username, $str_password_hash);
	}
	
	function query_credentials( $str_sql_query, $obj_db , $str_username, $str_password) 
	{
		// perform query
		$res_query = $obj_db->query_select( $str_sql_query );

		// check if user exists
		if ( !$res_query )
		{
			return null;
		}
		
		// authenticate - check with ldap directory

		// TEST , TESTING LOGIN , SEPT 27TH 2023
		// To disable login auth with LDAP, comment out this entire if block
		if (!self::ldap_login($str_username, $str_password))  
		{
			return null;
		}
		// TEST , TESTING LOGIN , SEPT 27TH 2023 ^^^
	

		// fetch user information
		$res_row = $obj_db->fetch( $res_query );
		
		// create return object
		$obj_user = new User;
		
		// fill object fields
		$obj_user->str_first_name = $res_row->FirstName;
		$obj_user->str_last_name  = $res_row->LastName;
		$obj_user->str_username = $res_row->UserId;
		$obj_user->int_privilege  = $res_row->PrivilegeLvl;
		
		return $obj_user;
	}

	function ldap_login($str_username, $str_password)
	{
		$ldap = LDAP_LOGIN_HOST;
		// TODO: change distinguished name(path to students username) 
		$usr = "uid=".$str_username.",ou=People,dc=landfood,dc=ubc,dc=ca";
		$ds = ldap_connect($ldap);
		ldap_set_option($ds, LDAP_OPT_NETWORK_TIMEOUT, 3);

		if ($ds) {
			set_error_handler(function(){});
			$result = ldap_bind($ds, $usr, $str_password);
			restore_error_handler();
			return $result;
		}

		return false;
	}
}

?>