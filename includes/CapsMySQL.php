<?php

/**
 * @package core
 * @subpackage database
 */

/**
 * Cut down MySQL database wrapper that emulates ADODB.
 */
class CapsMySQL {
	
	private $_c;
	
	/**
	 * Creates a new MySQL connection.
	 * @param	string $host The MySQL host
	 * @param	string $user The username
	 * @param	string $pass The password
	 * @param	string $db The name of the database
	 */
	public function __construct($host, $user, $pass, $db) {
		
		// Connect to the server
		$this->_c = @mysql_connect($host, $user, $pass);
		if (false === $this->_c)
			throw new CapsSQLException('MySQL Error: Unable to connect to MySQL server: "' . $host . '" with user "' . $user . '": ' . mysql_connect_error());
			
		// Connect to the database
		if (false === @mysql_select_db($db, $this->_c))
			throw new CapsSQLException('MySQL Error: "' . $db . '": ' . mysql_error($this->_c));
		
	}
	
	/**
	 * Returns the connection.
	 * @return	resource
	 */
	public function connection() {
		return $this->_c;
	}
	
	/**
	 * Returns the number of affected rows.
	 * @return	integer The number of affected rows from the last query.
	 */
	public function affected_rows() {
		return mysql_affected_rows($this->_c);
	}
	
	/**
	 * Returns the MySQL error number for the last query.
	 * @return	integer The MySQL error number
	 */
	public function errorno() {
		return mysql_errno($this->_c);
	}
	
	/**
	 * Returns a result set for the given query.
	 * @param	string $query The SQL query to run
	 * @param	array $params Any SQL parameters
	 * @return	CapsMySQLResultSet|boolean The results; or true for non select queries
	 * @throws	CapsSQLException If there is a problem with the query
	 */
	public function execute($query, $params = false) {
		global $system;

		// Prepare the statements
		if ($params) {
			if (!is_array($params))
				$params = array($params);
			else
				$params = array_values($params);
			$bits = explode('?', $query);
			$end = count($params);
			$query = '';
			foreach ($bits as $key => $val) {
				$value = array_key_exists($key, $params) ? $params[$key] : '';
				$query .= $val . ($key < $end ?
					((is_int($value) || is_float($value)) ? $value :
					(null === $value ? 'NULL' :
					(is_array($value) ? $this->qstr('Array') :
					$this->qstr($value)))) : '');
			}
		}
		
		// Execute the query
		$rs = mysql_query($query, $this->_c);
		
		// Check for errors
		if (mysql_error($this->_c))
			throw new CapsSQLException(mysql_error($this->_c) . ' in query "' . $query . '"', mysql_errno($this->_c));
			
		// If boolean return it
		if (false === $rs || true === $rs)
			return $rs;
			
		// Return the results
		return new CapsMySQLResultSet($rs);
	}
	
	/**
	 * Returns the complete result set as an array.
	 * @param	string $query The SQL query to run
	 * @param	array $params Any SQL parameters
	 * @return	array The result set as an array.
	 */
	public function getarray($query, $params = false) {
		return $this->execute($query, $params)->getarray();
	}
	
	/**
	 * Returns the value of the first column in the first row.
	 * @param	string $query The SQL query to run
	 * @param	array $params Any SQL parameters
	 * @return	mixed The value; or false if no value found
	 */
	public function getone($query, $params = false) {
		$row = $this->getrow($query, $params);
		if ($row == false)
			return false;
		foreach ($row as $v)
			return $v;
		return false;
	}
	
	/**
	 * Returns the first row from the result set.
	 * @param	string $query The SQL query to run
	 * @param	array $params Any SQL parameters
	 * @return	array The row; or false if no records found
	 */
	public function getrow($query, $params = false) {
		
		// Modify the query so it only returns a single record
		if (!preg_match('/LIMIT\s+\d+$/i', $query) && preg_match('/^SELECT/i', $query))
			$query .= ' LIMIT 1';
		
		// Return the first row
		$results = $this->execute($query, $params);
		if (false === $results || true === $results) {
			return false;
		}
		
		return $results->fetchrow();
	}
	
	/**
	 * Returns the id of the last created row.
	 * @return	integer The id; or false if none found
	 */
	public function insert_id() {
		return mysql_insert_id($this->_c);
	}
	
	/**
	 * Alias of execute() function.
	 * @deprecated	Use execute instead
	 */
	public function query($sql, $params = false) {
		return $this->execute($sql, $params);
	}
	
	//======================= UTILITY ============================
	
	/**
	 * Returns the MySQL version of a date.
	 * @param	integer $timestamp The timestamp
	 * @return	string The date in ISO format
	 */
	public function binddate($timestamp) {
		return date('Y-m-d', $timestamp);
	}
	
	/**
	 * Returns the MySQL version of a date and time.
	 * @param	integer $timestamp The timestamp to convert
	 * @return	string The date and time in ISO format (without time zone)
	 */
	public function bindtimestamp($timestamp) {
		return date('Y-m-d H:i:s', $timestamp);
	}
	
	/**
	 * Returns the MySQL version of a date and time in the UTC timezone.
	 * @param	integer $timestamp The timestamp to convert
	 * @return	string The date and time in ISO format (UTC timezone)
	 */
	public function bindtimestamputc($timestamp) {
		return gmdate('Y-m-d H:i:s', $timestamp);
	}
	
	/**
	 * Quotes a string.
	 * @param	string $string The string to quote
	 * @return	string The quoted string
	 */
	public function qstr($string) {
		return "'" . mysql_real_escape_string($string, $this->_c) . "'";
	}
	
	/**
	 * Returns the UNIX timestamp for a given date.
	 * @param	string $date The ISO date
	 * @return	integer The timestamp; or 0 if invalid
	 */
	public function unixdate($date) {
		$value = strtotime($date);
		$invalid = strtotime('1970-01-01');
		if ($value <= $invalid)
			return 0;
		return $value;
	}
	
	/**
	 * Returns the UNIX timestamp for a given time.
	 * @param	string $date The ISO date
	 * @return	integer The timestamp; or 0 if invalid
	 */
	public function unixtimestamp($date) {
		return $this->unixdate($date);
	}
	
	//===================== TRANSACTIONS ==========================
	
	/**
	 * Starts a transaction (disabled at this point).
	 * @return	boolean True if successful; false otherwise
	 * @todo	Implement transactional stuff
	 */
	public function starttrans() {
		return true;
	}
	
	/**
	 * Completes a transaction (disabled at this point).
	 * @return	boolean True if sucessful; false otherwise
	 * @todo	Implement transactional stuff
	 */
	public function completetrans() {
		return true;
	}
	
}

/**
 * Represents a MySQL result set.
 */
class CapsMySQLResultSet implements Iterator {
	
	private $_r = false;
	private $_k = -1;
	private $_v = false;
	private $_c = false;
	
	/**
	 * Creates a new result set.
	 * @param	resource $results The mySQL result set
	 */
	public function __construct($results) {
		$this->_r = $results;
	}
	
	/**
	 * Returns the result set source.
	 * @return	resource
	 */
	public function resource() {
		return $this->_r;
	}
	
	/**
	 * Executes the mysql_fetch_assoc function but retains legacy behaviour
	 * where false is returned instead of null.
	 */
	private function _fetch_assoc() {
		$row = mysql_fetch_assoc($this->_r);
		return null === $row ? false : $row;
	}
	
	/**
	 * Moves to the next row.
	 */
	private function _fetch() {
		if ($this->_r) {
			$this->_k++;
			$this->_v = $this->_fetch_assoc();
		}
	}
	
	/**
	 * Returns the current row from the record set.
	 * @return	array The current row; or false if none found
	 */
	public function current() {
		if ($this->_k < 0)
			$this->_fetch();
		return $this->_v;
	}
	
	/**
	 * Moves to the next row in the record set.
	 */
	public function next() {
		$this->_fetch();
	}
	
	/**
	 * Returns the current key.
	 */
	public function key() {
		return $this->valid() ? $this->_k : null;
	}
	
	/**
	 * Indicates if the current row is valid.
	 */
	public function valid() {
		return false !== $this->_v && null !== $this->_v;
	}
	
	/**
	 * Moves to the start of the record set.
	 */
	public function rewind() {
		if ($this->_k < 0) {
			$this->_fetch();
		} elseif ($this->_r) {
			$this->_k = -1;
			if ($this->count() > 0)
				mysql_data_seek(0, $this->_r);
			$this->_fetch();
		}
	}
	
	/**
	 * Returns the total number of records in the set.
	 */
	public function count() {
		if ($this->_r) {
			if (false === $this->_c)
				$this->_c = mysql_num_rows($this->_r);
			return $this->_c;
		}
		return 0;
	}
	
	// =============== ADODB COMPATIBILITY ===========================
	
	/**
	 * Alias of count().
	 */
	public function numrows() {
		return $this->count();
	}
	
	/**
	 * Alias of count().
	 */
	public function recordcount() {
		return $this->count();
	}
	
	/**
	 * Alias of count().
	 */
	public function rowcount() {
		return $this->count();
	}
	
	/**
	 * Closes the record set.
	 */
	public function close() {
		if ($this->_r) {
			mysql_free_result($this->_r);
			$this->_r = null;
			$this->_k = -1;
			$this->_c = false;
			$this->_v = false;
		}
	}
	
	/**
	 * Moves to a specific spot in the result set.
	 * @param	integer $pos The spot (0 = start)
	 */
	public function move($pos) {
		if ($this->_r) {
			$r = mysql_data_seek($pos, $this->_r);
			$this->_k = $pos;
			$this->_v = $this->_fetch_assoc();
		}
	}
	
	/**
	 * Moves to the first position.
	 */
	public function movefirst() {
		$this->move(0);
	}
	
	/**
	 * Returns the next row in the result set and moves the pointer.
	 * @return	array The next row; or false if there are no more rows
	 */
	public function fetchrow() {
		$c = $this->current();
		$this->next();
		return $c;
	}
	
	/**
	 * Returns the result set as an array.
	 * @return	array The result sets
	 */
	public function getarray() {
		$results = array();
		$this->rewind();
		while ($this->valid()) {
			$results[] = $this->current();
			$this->next();
		}
		return $results;
	}
	
	/**
	 * Returns the current row.
	 * @return	array The current row
	 */
	public function currentrow() {
		return $this->key();
	}
	
}

/**
 * Represents an SQL error.
 */
class CapsSQLException extends Exception {
	
	public function __construct($message, $code) {
		parent::__construct('SQL Error: ' . $code . ' ' . $message, $code);
	}
	
}
