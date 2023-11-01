<?php

define( 'MSG_SUCCESS', 0 );
define( 'MSG_FAIL', 1 );
define( 'MSG_ERROR', 2 );

$g_arr_messages = array();
$g_arr_messages[MSG_SUCCESS] = array();
$g_arr_messages[MSG_FAIL] = array();
$g_arr_messages[MSG_ERROR] = array();

class MessageHandler
{
	/**  Function: add_message( $int_message_type, $str_message )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Add the message to the list
	*    Arguments:         $int_message_type - int, the message type
	*                       $str_message - string, the message
	*                       
	*    Returns/Assigns:   None
	*/
	function add_message( $int_message_type, $str_message )
	{
		global $g_arr_messages;

		array_push( $g_arr_messages[$int_message_type], $str_message );
	}

	/**  Function: has_message( $int_message_type )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Determine whether there is any message
	*    Arguments:         $int_message_type - int, the message type
	*                       
	*    Returns/Assigns:   Returns true if there is a message;
	*                       false otherwise
	*/
	function has_message( $int_message_type )
	{
		global $g_arr_messages;

		return count( $g_arr_messages[$int_message_type] ) != 0;
	}

	/**  Function: next_message( $int_message_type )
	*    ---------------------------------------------------------------- 
	*    Purpose:           Populates the next message
	*    Arguments:         $int_message_type - int, the message type
	*                       
	*    Returns/Assigns:   Returns the next message
	*/
	function next_message( $int_message_type )
	{
		global $g_arr_messages;

		if ( (new MessageHandler) ->  has_message( $int_message_type ) )
		{
			return array_pop( $g_arr_messages[$int_message_type] );
		}

		return null;
	}
}

?>
