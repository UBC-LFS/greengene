<?php

/**
 * DB class
 *
 * @author Stanley Tso
 * @package DBManager
 */
class DB
{
	var $m_conn;

	/**
	 * DB: Default constructor which will establish database connection
	 * PRE: require /include/config.php for database info (DBHOST, DBUSER, DBPWD, DBNAME)
	 * POST: connection established & DB has been selected.
	 */
	function __construct() // in php8, constructors must be called like this
	{
		// Create connection
		$this->m_conn = mysqli_init();
		if (!$this->m_conn ) {
			die("Could not connect to MySQL server.");
		}

		mysqli_ssl_set($this->m_conn ,
						SSL_KEY_PATH, 
						SSL_CERTIFICATE_PATH,
						SSL_CA_CERTIFICATE_PATH,
						null,
						null);

		// MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT is only for dev, because the CN names will be different
		if (!mysqli_real_connect($this->m_conn , DBHOST, DBUSER, DBPWD, DBNAME, null, null, MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT)) {
			die("Connect Error:  " . mysqli_connect_error());
		}

	}

	/**
	 * querySelect: this query is for Selection queries
	 * PRE: $SQL is contained a VALID SELECT SQL statement. (Syntax has already checked before sending into this method.
	 * POST: a recordset resource has returned.
	 * @param string $p_SQL
	 * @return dbResourceIdentifier
	 */
	function querySelect($p_SQL)
	{
		$l_resource = mysqli_query($this->m_conn,$p_SQL);
		//$l_resource = mysql_query($p_SQL)
			//or Logger::writeLog('MySQL Query Error (' . mysql_errno() . '): ' . mysql_error() . "\n$p_SQL");
		return $l_resource;
	}

	 /**
	 * queryCommit: this is for insert/replace/update/delete SQL queries. return success on record affected.
	 * PRE: $SQL is contained a VALID INSERT/REPLACE/UPDATE/DELETE SQL statement.(Syntax has already checked before sending into this method.)
	 * POST: a boolean indicating sucessful or failure.
	 * @param string $p_SQL
	 * @return boolean
	 */
	function queryCommit($p_SQL)
	{
		$l_resource = mysqli_query($this -> m_conn, $p_SQL );

		//$l_resource = mysql_query($p_SQL, $this -> m_conn)
			//or Logger::writeLog('MySQL Commit Error (' . mysql_errno() . '): ' . mysql_error() . "\n$p_SQL");
		if (mysqli_errno($this->m_conn) == 0)
		//if (mysql_errno() == 0)
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	 /**
	 * fetch: this method fetches a row of data from the database record.
	 * PRE: a valid resource (recordset)
	 * POST: return an array of the row objects from the resource (resource will automatically iterate to the next row)
	 * @param recordset $p_resource
	 * @return recordRowData
	 * @deprecated Usage: $row = $DB -> fetch($myresource);echo $row -> fieldName;  //display the datafield of the corresponding row
	 */
	function fetch($p_resource)
	{
		return mysqli_fetch_object($p_resource);
	}

	 /**
	 * getNumRows: This method returns number of row selected from SELECT query
	 * PRE: a valid resource from a SELECT SQL statement
	 * POST: return number of rows selected from the query
	 * @param recordset $p_resource
	 * @return int
	 */
	function getNumRows($p_resource)
	{
		return mysqli_num_rows($p_resource);
	}

	 /**
	 * getRowsAffected: This method returns number of row selected from insert/replace/update/delete SQL queries
	 * PRE: last query using this class is one of insert/replace/update/delete SQL query.
	 * POST: return the number of rows affected
	 * @return int
	 */
	function getRowsAffected()
	{
		return mysqli_affected_rows($this -> m_conn);
	}

	 /**
	 * sqlString: This method change any sensitive charactor into SQL readable charactor
	 * PRE: none
	 * POST: return the encoded string
	 * @param string $p_string
	 * @return String
	 */
	function sqlString($p_string)
	{
		// function is deprecated. will always return false
		// if (get_magic_quotes_gpc())
		if (false)
			return mysqli_real_escape_string($this->m_conn, stripslashes($p_string));
		return mysqli_real_escape_string($this->m_conn,$p_string);
	}


	function getLastInsertId()
	{
		return mysqli_insert_id($this -> m_conn);
	}

	 /**
	 * disconnect: This method disconnect the current database connection.
	 * PRE: an active database connection.
	 * POST: db connection disconnected.
	 */
	function disconnect()
	{
		mysqli_close($this -> m_conn);
	}
}
?>
