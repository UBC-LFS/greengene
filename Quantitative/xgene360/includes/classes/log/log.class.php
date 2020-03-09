<?php

class Log
{
	/**  Function: void write_log( $log_type, $str_message )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Write the log to the file
	*    Arguments:         $log_type - string, the log filename
	*                       $str_message - string, the message
	*
	*    Returns/Assigns:   None
	*/
	function write_log( $log_type, $str_message )
	{
		$str_formatted_message = sprintf( "[%s] %s\r", date( 'Y-m-d H:i:s' ), $str_message );

		error_log( $str_formatted_message, 3, $log_type); 
	}
	
	/**  Function: void write_log_with_ip( $log_type, $str_message )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Write the log to the file with IP
	*    Arguments:         $log_type - string, the log filename
	*                       $str_message - string, the message
	*
	*    Returns/Assigns:   None
	*/
	function write_log_with_ip( $log_type, $str_message )
	{
		$str_formatted_message = sprintf( "[%s] [%s] %s\r", date( 'Y-m-d H:i:s' ), log::get_IP(), $str_message );

		error_log( $str_formatted_message, 3, $log_type); 
	}

	/**  Function: string get_IP()
	*    ---------------------------------------------------------------- 
	*    Purpose:           Get the IP of the request user
	*    Arguments:         None
	*
	*    Returns/Assigns:   The IP of the user
	*/
	function get_IP()
	{
		$str_ip = '';

		if ( getenv( 'HTTP_CLIENT_IP' ) )
		{
			$str_ip = getenv( 'HTTP_CLIENT_IP' );
		}

		else if ( getenv( 'HTTP_X_FORWARDED_FOR' ) )
		{
			$str_ip = getenv( 'HTTP_X_FORWARDED_FOR' );
		}
		
		else if ( getenv( 'REMOTE_ADDR' ) )
		{
			$str_ip = getenv( 'REMOTE_ADDR' );
		}

		else
		{
			$str_ip = 'Unknwon';
		}

		return $str_ip; 
	}
}

?>
