<?php

class DBManager
{
	var $m_obj_connection;

	/**
	 *
	 * create connection to the sql database
	 *
	 */
	function DBManager()
	{
	  $this->m_obj_connection = mysqli_connect( DB_SERVER, DB_USERNAME, DB_PASSWORD );
	  
	  if ( $this->m_obj_connection == false )
	  {
	    Log::write_log( LOG_SQL, mysqli_errno($this->m_obj_connection).': '.mysqli_error($this->m_obj_connection) );
	    die( "Cannot connect to MySQL server" );
	  }
	  
		mysqli_select_db( $this->m_obj_connection, DB_NAME );
		
		if( mysqli_errno($this->m_obj_connection) != 0 )
		{
			Log::write_log( LOG_SQL, mysqli_errno($this->m_obj_connection).': '.mysqli_error($this->m_obj_connection) );
			die( "Cannot select MySQL database" );
		}
	}

	/**
	 * 
	 * for selection queries
	 *
	 * @param string $str_SQL the sql statement
	 * @return resource the resource returned from sql query
	 *
	 */
	function query_select( $str_SQL )
	{
		
		$tmp_resource = mysqli_query( $this->m_obj_connection, $str_SQL )
		  or Log::write_log( LOG_SQL, mysqli_errno($this->m_obj_connection).': '.mysqli_error($this->m_obj_connection).'\n'.$str_SQL );
			
		return $tmp_resource;
	}

	/**
	*
	* commit queries
	*
	* @param string $str_SQL the sql statement
	* @return boolean whether the commit is succeeded
	*
	*/
	function query_commit( $str_SQL )
	{
		$tmp_resource = mysqli_query( $this->m_obj_connection, $str_SQL )
			or Log::write_log( LOG_SQL, mysqli_errno($this->m_obj_connection).': '.mysqli_error($this->m_obj_connection).'\n'.$str_SQL );
		
		return ( mysqli_errno($this->m_obj_connection) == 0 );
	}

	/**
	*
	* fetches the rows from the database
	*
	* @param resource $res the resource from SELECT query
	* @return mixed properties that correspond to the fetched row, or FALSE is there are no more rows.
	*
	*/
	function fetch( $res )
	{
		return mysqli_fetch_object( $res );
	}

	/**
	*
	* get number of rows from SELECT query
	*
	* @param resource $res the resource from SELECT query
	* @return int the number of rows
	*
	*/
	function get_number_of_rows( $res )
	{
		return mysqli_num_rows( $res );
	}

	/**
	*
	* get number of rows affected by insert/replace/update/delete queries
	*
	* @return int the number of rows affected
	*
	*/
	function get_number_of_rows_affected()
	{
		return mysqli_affected_rows( $this->m_obj_connection );
	}

	/**
	*
	* format the sql string to SQL readable character
	*
	* @param string $str the string need to be replaced
	* @return string formatted string that's readable to SQL
	*
	*/
	function format_sql_string( $str )
	{
		if ( get_magic_quotes_gpc() )
		{
			return mysqli_real_escape_string($this->m_obj_connection, stripslashes( $str ) );
		}
		
		return mysqli_real_escape_string( $this->m_obj_connection, $str );
	}

  /**
  * 
  * get the last inserted row id
  *
  * @return int the id of the last inserted row
  *
  */
	function get_last_inserted_id()
	{
		return mysqli_insert_id($this->m_obj_connection);
	}

	 /**
	 *
	 * disconnect the current database connection
	 *
	 */
	function disconnect()
	{
		mysqli_close( $this->m_obj_connection );
	}
	
	
	/**
	 *
	 * returns the database server time
	 *
	 */
	function time()
	{
		// anybody should be able to get server time
		$str_sql_query = "SELECT UNIX_TIMESTAMP( T.time ) AS time "
		               . "FROM ( SELECT NOW() AS time ) AS T ";
		               
		$res_result = $this->query_select( $str_sql_query );
		
		$res_row = $this->fetch( $res_result );
		
		// convert to GMT time
		$time = $res_row->time - date( "Z" );
		
		// are we in daylight saving time?
		$time = $time + date( "I" )  * 3600;
		
		// convert to user time
		$time = $time + USER_TIME_ZONE * 3600;
		
		return $time;
	}
}
?>
