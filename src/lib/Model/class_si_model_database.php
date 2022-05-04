<?php

class Si_Model_Database extends Si_Model {

	private $_host;
	private $_port;
	private $_user;
	private $_password;
	private $_name;
	private $_tbl_prefix;

	private $_config;

	private $_handle;

	private function __construct () {}

	public static function from_config (Si_Model_Wpconfig $conf) {
		$me = new self;
		$env = new Si_Model_Env;

		$_dbhost = $env->can_override() ? $env->get('_dbhost') : false;
		$me->_host = !empty($_dbhost) ? $_dbhost : $conf->get('DB_HOST');

		if (false !== strpos($me->_host, ':')) {
			$tmp = explode(':', $me->_host, 2);
			if (!empty($tmp[1]) && is_numeric($tmp[1])) {
				$me->_host = $tmp[0];
				$me->_port = $tmp[1];
			}
		}

		$_dbuser = $env->can_override() ? $env->get('_dbuser') : false;
		$me->_user =  !empty($_dbuser) ? $_dbuser : $conf->get('DB_USER');

		$_dbpassword = $env->can_override() ? $env->get('_dbpassword') : false;
		$me->_password =  !empty($_dbpassword) ? $_dbpassword : $conf->get('DB_PASSWORD');

		$_dbname = $env->can_override() ? $env->get('_dbname') : false;
		$me->_name =  !empty($_dbname) ? $_dbname : $conf->get('DB_NAME');

		$_dbtable_prefix = $env->can_override() ? $env->get('_dbtable_prefix') : false;
		$me->_tbl_prefix =  !empty($_dbtable_prefix) ? $_dbtable_prefix : $conf->get('$table_prefix');

		$me->_config = $conf;

		return $me;
	}

	/**
	 * Connects to the database with credentials already set
	 *
	 * @return bool
	 */
	public function connect () {
		if ($this->_handle) return true;

		$port = !empty($this->_port) && is_numeric($this->_port)
			? $this->_port
			: ini_get("mysqli.default_port")
		;

		$this->_handle = @mysqli_connect($this->_host, $this->_user, $this->_password, $this->_name, (int)$port);

		return !!$this->_handle;
	}

	/**
	 * Gets DB connection error code
	 *
	 * @return int
	 */
	public function get_connection_error_code () {
		return mysqli_connect_errno();
	}

	/**
	 * Check if database is empty (has no tables)
	 *
	 * @return bool
	 */
	public function is_db_empty () {
		if (!$this->_handle) return true; // Invalid handle
		$rows = mysqli_query($this->_handle, "SELECT table_name FROM information_schema.tables WHERE table_schema = '{$this->_name}'");
		return $rows
		 	? !mysqli_num_rows($rows)
			: true
		;
	}

	/**
	 * Gets table prefix
	 *
	 * @return string Table prefix
	 */
	public function get_prefix () {
		return $this->_tbl_prefix;
	}

	/**
	 * Restores table from SQL file
	 *
	 * @param string $path Full path to SQL file
	 *
	 * @return bool
	 */
	public function restore_from_file ($path) {
		if (!file_exists($path) || !is_readable($path)) return false;
		$sql = file_get_contents($path);

		$old_pfx = $this->_config->get('$table_prefix');
		if ($old_pfx !== $this->_tbl_prefix) {
			$src_name = basename($path, '.sql');
			$old_pfx = preg_quote($old_pfx, '/');
			$dest_name = preg_replace("/{$old_pfx}/", $this->_tbl_prefix, $src_name);
			if ($src_name !== $dest_name) {
				$esc_name = preg_quote("`{$src_name}`", '/');
				$sql = preg_replace("/{$esc_name}/", "`{$dest_name}`", $sql);
			}
		}

		return !empty($sql)
			? $this->restore_from_sql($sql)
			: false
		;
	}

	/**
	 * Restores table from SQL buffer
	 *
	 * @param string $sql Buffer
	 *
	 * @return bool
	 */
	public function restore_from_sql ($sql) {
		if (empty($sql)) return false;
		if (!$this->connect()) return false;

		return $this->restore_databases($sql);
	}

	/**
	 * Perform a query
	 *
	 * @param string $sql SQL query to perform
	 *
	 * @return bool
	 */
	public function query ($sql) {
		if (!$this->connect()) return false;

/*
// ---------------------------------------------------------
// Collation fix for compatibility reasons with older MySQLs
// ---------------------------------------------------------
		// Try to clean up the collations
		$sql = preg_replace('/\butf8mb4_unicode_520_ci\b/', 'utf8_unicode_ci', $sql);
		// Now try to clean up charsets
		$sql = preg_replace('/\bCHARSET=utf8mb4\b/', 'CHARSET=utf8', $sql);
*/
		$result = mysqli_query($this->_handle, $sql);
		if (false === $result) {
			$log_query = trim($sql);
			if (!!preg_match('/\binsert\b/i', $sql)) {
				preg_match('/\binto\s+(\S+?)\s/i', $sql, $matches);
				$tbl = !empty($matches[1]) ? "(into {$matches[1]})" : "";
				$log_query = "An insert {$tbl} query";
			}
			Si_Helper_Log::log("[[Query]] {$log_query}");
			Si_Helper_Log::log("Error: " . $this->last_query_error());
		}

		return false !== $result;
	}

	/**
	 * Return string description of the last error
	 *
	 * @return string
	 */
	public function last_query_error () {
		return mysqli_error($this->_handle);
	}






	function restore_databases( $buffer ) {
		$sql                         = '';
		$start_pos                   = 0;
		$i                           = 0;
		$len                         = 0;
		$big_value                   = 2147483647;
		$delimiter_keyword           = 'DELIMITER '; // include the space because it's mandatory
		$length_of_delimiter_keyword = strlen( $delimiter_keyword );
		$sql_delimiter               = ';';
		$finished                    = false;

		$status = true;

		$len = strlen( $buffer );

		$this->query("SET SQL_MODE='ALLOW_INVALID_DATES';");
		$this->query("SET FOREIGN_KEY_CHECKS=0");
		$this->query("SET NAMES utf8mb4");

		// Grab some SQL queries out of it
		while ( $i < $len ) {
			//@set_time_limit( 300 );

			$found_delimiter = false;

			// Find first interesting character
			$old_i = $i;

			// this is about 7 times faster that looking for each sequence i
			// one by one with strpos()
			if ( preg_match( '/(\'|"|#|-- |\/\*|`|(?i)(?<![A-Z0-9_])' . $delimiter_keyword . ')/', $buffer, $matches, PREG_OFFSET_CAPTURE, $i ) ) {
				// in $matches, index 0 contains the match for the complete
				// expression but we don't use it

				$first_position = $matches[1][1];
			} else {
				$first_position = $big_value;
			}

			$first_sql_delimiter = strpos( $buffer, $sql_delimiter, $i );
			if ( $first_sql_delimiter === false ) {
				$first_sql_delimiter = $big_value;
			} else {
				$found_delimiter = true;
			}

			// set $i to the position of the first quote, comment.start or delimiter found
			$i = min( $first_position, $first_sql_delimiter );
			//echo "i=[". $i ."]<br />";

			if ( $i == $big_value ) {
				// none of the above was found in the string

				$i = $old_i;
				if ( ! $finished ) {
					break;
				}

				// at the end there might be some whitespace...
				if ( trim( $buffer ) == '' ) {
					$buffer = '';
					$len    = 0;
					break;
				}

				// We hit end of query, go there!
				$i = strlen( $buffer ) - 1;
			}

			// Grab current character
			$ch = $buffer[ $i ];

			// Quotes
			if ( strpos( '\'"`', $ch ) !== false ) {
				$quote = $ch;
				$endq  = false;

				while ( ! $endq ) {
					// Find next quote
					$pos = strpos( $buffer, $quote, $i + 1 );

					// No quote? Too short string
					if ( $pos === false ) {
						// We hit end of string => unclosed quote, but we handle it as end of query
						if ( $finished ) {
							$endq = true;
							$i    = $len - 1;
						}

						$found_delimiter = false;
						break;
					}

					// Was not the quote escaped?
					$j = $pos - 1;

					while ( $buffer[ $j ] == '\\' ) {
						$j --;
					}

					// Even count means it was not escaped
					$endq = ( ( ( ( $pos - 1 ) - $j ) % 2 ) == 0 );

					// Skip the string
					$i = $pos;

					if ( $first_sql_delimiter < $pos ) {
						$found_delimiter = false;
					}
				}

				if ( ! $endq ) {
					break;
				}

				$i ++;

				// Aren't we at the end?
				if ( $finished && $i == $len ) {
					$i --;
				} else {
					continue;
				}
			}

			// Not enough data to decide
			if ( ( ( $i == ( $len - 1 ) && ( $ch == '-' || $ch == '/' ) )
			       || ( $i == ( $len - 2 ) && ( ( $ch == '-' && $buffer[ $i + 1 ] == '-' )
			                                    || ( $ch == '/' && $buffer[ $i + 1 ] == '*' ) ) ) ) && ! $finished
			) {
				break;
			}


			// Comments
			if ( $ch == '#'
			     || ( $i < ( $len - 1 ) && $ch == '-' && $buffer[ $i + 1 ] == '-'
			          && ( ( $i < ( $len - 2 ) && $buffer[ $i + 2 ] <= ' ' )
			               || ( $i == ( $len - 1 ) && $finished ) ) )
			     || ( $i < ( $len - 1 ) && $ch == '/' && $buffer[ $i + 1 ] == '*' )
			) {
				// Copy current string to SQL
				if ( $start_pos != $i ) {
					$sql .= substr( $buffer, $start_pos, $i - $start_pos );
				}

				// Skip the rest
				$start_of_comment = $i;

				// do not use PHP_EOL here instead of "\n", because the export
				// file might have been produced on a different system
				$i = strpos( $buffer, $ch == '/' ? '*/' : "\n", $i );

				// didn't we hit end of string?
				if ( $i === false ) {
					if ( $finished ) {
						$i = $len - 1;
					} else {
						break;
					}
				}

				// Skip *
				if ( $ch == '/' ) {
					$i ++;
				}

				// Skip last char
				$i ++;

				// We need to send the comment part in case we are defining
				// a procedure or function and comments in it are valuable
				$sql .= substr( $buffer, $start_of_comment, $i - $start_of_comment );

				// Next query part will start here
				$start_pos = $i;

				// Aren't we at the end?
				if ( $i == $len ) {
					$i --;
				} else {
					continue;
				}
			}

			// Change delimiter, if redefined, and skip it (don't send to server!)
			if ( strtoupper( substr( $buffer, $i, $length_of_delimiter_keyword ) ) == $delimiter_keyword
			     && ( $i + $length_of_delimiter_keyword < $len )
			) {
				// look for EOL on the character immediately after 'DELIMITER '
				// (see previous comment about PHP_EOL)
				$new_line_pos = strpos( $buffer, "\n", $i + $length_of_delimiter_keyword );

				// it might happen that there is no EOL
				if ( false === $new_line_pos ) {
					$new_line_pos = $len;
				}

				$sql_delimiter = substr( $buffer, $i + $length_of_delimiter_keyword, $new_line_pos - $i - $length_of_delimiter_keyword );
				$i             = $new_line_pos + 1;

				// Next query part will start here
				$start_pos = $i;
				continue;
			}

			if ( $found_delimiter || ( $finished && ( $i == $len - 1 ) ) ) {
				$tmp_sql = $sql;

				if ( $start_pos < $len ) {
					$length_to_grab = $i - $start_pos;

					if ( ! $found_delimiter ) {
						$length_to_grab ++;
					}

					$tmp_sql .= substr( $buffer, $start_pos, $length_to_grab );
					unset( $length_to_grab );
				}

				// Do not try to execute empty SQL
				if ( ! preg_match( '/^([\s]*;)*$/', trim( $tmp_sql ) ) ) {
					$sql = $tmp_sql;
					//echo "sql=[". $sql ."]<br />";
					$ret_db = $this->query( $sql );
					if (!$ret_db) $status = false;
					//echo "ret_db<pre>"; print_r($ret_db); echo "</pre>";

					$buffer = substr( $buffer, $i + strlen( $sql_delimiter ) );
					// Reset parser:

					$len       = strlen( $buffer );
					$sql       = '';
					$i         = 0;
					$start_pos = 0;

					// Any chance we will get a complete query?
					//if ((strpos($buffer, ';') === FALSE) && !$GLOBALS['finished']) {
					if ( ( strpos( $buffer, $sql_delimiter ) === false ) && ! $finished ) {
						break;
					}
				} else {
					$i ++;
					$start_pos = $i;
				}
			}

		}

		return $status;

	}


}
