<?php
/**
 * Logger class
 *
 * @author Brett Taylor, Stanley Tso
 * @package LogManager
 */
class Logger
{
	/**
	 * Write error to log file
	 *
	 * @param string $p_error log message
	 */
	function writeLog($p_error)
	{
		error_log(date("F j, Y, g:i a\: \E\R\R\O\R\: ") . $p_error . "\n", 3, LOGPATH);
	}
}

?>
