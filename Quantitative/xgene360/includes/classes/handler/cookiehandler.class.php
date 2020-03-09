<?php

define( 'COOKIE_USER', 'xgene360_user' );

class CookieHandler
{
	/**  Function: void set_user( $obj_user )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Set the user object to the session
	*    Arguments:         $obj_user - object, the user object
	*                       
	*    Returns/Assigns:   None
	*/
	function set_user( $obj_user )
	{
		$_SESSION[COOKIE_USER] = $obj_user;
	}

	/**  Function: void unset_user()
	*    ---------------------------------------------------------------- 
	*    Purpose:           Unset the user object from the session
	*    Arguments:         None
	*                       
	*    Returns/Assigns:   None
	*/
	function unset_user()
	{
		if ( isset( $_SESSION ) )
		{
			if ( isset( $_SESSION[COOKIE_USER] ) )
			{
				unset( $_SESSION[COOKIE_USER] );
			}
			
			$_SESSION = array();
		}

		session_destroy();
	}

	/**  Function: void get_user()
	*    ---------------------------------------------------------------- 
	*    Purpose:           Get the user object from the session
	*    Arguments:         None
	*                       
	*    Returns/Assigns:   Returns the user object
	*/
	function get_user()
	{
		if ( !isset( $_SESSION[COOKIE_USER] ) )
		{
			return null;
		}

		return $_SESSION[COOKIE_USER];
	}

	/**  Function: void set_cookie_value( $str_name, $obj_value )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Set the value to the cookie
	*    Arguments:         $str_name - string, the name of the value
	*                       $obj_value - object, the value 
	*                       
	*    Returns/Assigns:   None
	*/
	function set_cookie_value( $str_name, $obj_value )
	{
		setcookie( $str_name, $obj_value );
	}

	/**  Function: void get_cookie_Value( $str_name )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Get the value from the cookie
	*    Arguments:         $str_name - string, the name of the value
	*                       
	*    Returns/Assigns:   Returns the value
	*/
	function get_cookie_value( $str_name )
	{
		if ( !isset( $_COOKIE[$str_name] ) )
		{
			return null;
		}

		return $_COOKIE[$str_name];
	}
}
  
?>
