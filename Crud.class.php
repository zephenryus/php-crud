<?php

/**
 * Create-Read-Update-Delete Package
 *
 * Long description for file (if any)...
 *
 * PHP version 5
 *
 * The MIT License (MIT)
 *
 * Copyright (c) 2010 Kevin Jensen
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @category   Database
 * @package    CRUD
 * @author     Kevin Jensen <kevin@thepenta.com>
 * @copyright  2007-2010 Kevin Jensen / Ventureware
 * @license    http://opensource.org/licenses/MIT  The MIT License (MIT)
 * @link       ...
 * @since      File available since Release 1.0.0
 * version     1.0.0
 */

// {{{ CRUD

/**
 * Create-Read-Update-Delete Class
 *
 * The purpose of this class is to create a public interface with a MySQL
 * database which is more intuitive and PHP programming centric. Rather than
 * submitting SQL queries to the database, associative arrays containing values
 * are passed to the methods which are then parsed into create, read, update and
 * delete queries.
 *
 * @category   Database
 * @package    CRUD
 * @author     Kevin Jensen <kevin@thepenta.com>
 * @copyright  2007-2010 Ventureware /
 * @license    http://www.gnu.org/licenses/  GNU General Public License
 * @version    Release: 1.0.0
 * @link       ...
 * @see
 * @since      Class available since Release 1.0.0
 */
class crud {
	// {{{ Properties

	/**
	 * @var resource Pointer to a database connection resource
	 */
	private $resource;

	/**
	 * @var string Default database
	 */
	private $_database = 'schedule';

	/**
	 * @var string Default host
	 */
	private $_host = 'localhost';

	/**
	 * @var string Default password
	 */
	private $_pass = 'password';

	/**
	 * @var string Default user
	 */
	private $_user = 'cgi';

	// }}}
	// {{{ __construct

	/**
	 * Creates an instance of the CRUD object and opens or reuses a connection
	 * to a database.
	 *
	 * All paramesters are optional. Parameter values should be such that they
	 * would work in a mysqli::__construct() or mysql_connect() function.
	 *
	 * @param string $host     Host of a database
	 * @param string $user     User to login to a database
	 * @param string $pass     Password to login to a database
	 * @param string $database Database to establish connection with
	 *
	 * @access public
	 * @see http://us3.php.net/manual/en/mysqli.connect.php
	 * @since Method available since Release 1.0.0
	 */
	public function __construct ( $host = '', $user = '', $pass = '', $database = '' ) {

		if ( !empty( $host ) ) {
			$this->_host = $host;
		} elseif ( empty( $this->_host ) ) {
			throw new Exception( '<strong>Error:</strong> No host defined for SQL connection' );
		}

		if ( !empty( $user ) ) {
			$this->_user = $user;
		} elseif ( empty( $this->_user ) ) {
			throw new Exception( '<strong>Error:</strong> No user defined for SQL connection' );
		}

		if ( !empty( $pass ) ) {
			$this->_pass = $pass;
		} elseif ( empty( $this->_pass ) ) {
			throw new Exception( '<strong>Error:</strong> No password defined for SQL connection' );
		}

		if ( !empty( $database ) ) {
			$this->_database = $database;
		} elseif ( empty( $this->_database ) ) {
			throw new Exception( '<strong>Error:</strong> No database defined for SQL connection' );
		}

		$this->resource = new mysqli( $this->_host, $this->_user, $this->_pass, $this->_database );

	}

	public function __destruct () {
		$this->resource->close();
	}
	// }}}
	// {{{ create

	/**
	 * Creates and executes an INSERT SQL query from an associative array
	 *
	 * The associative array submitted to this method should follow a naming
	 * convention of key => value where key is the column name into which the
	 * value would be inserted:
	 *
	 * Array (
	 *    'firstName' => 'Foo',
	 *    'lastName' => 'Bar',
	 *    'department' => 'Foobar'
	 * )
	 *
	 * Would yield the following result (or something similar):
	 *
	 * +-------------+-----------+----------+------------+
	 * | employee_id | firstName | lastName | department |
	 * +-------------+-----------+----------+------------+
	 * | 859648      | Foo       | Bar      | Foobar     |
	 * +-------------+-----------+----------+------------+
	 *
	 * The method is designed to recursively process multidimensional arrays:
	 *
	 * Array (
	 *     [0] => Array (
	 *         'firstName' => 'Foo',
	 *         'lastName' => 'Bar',
	 *         'department' => 'Foobar'
	 *     )
	 *     [1] => Array (
	 *         'firstName' => 'Fooy',
	 *         'lastName' => 'Barington',
	 *         'department' => 'Sales'
	 *     )
	 * )
	 *
	 * Would yield:
	 *
	 * +-------------+-----------+-----------+------------+
	 * | employee_id | firstName | lastName  | department |
	 * +-------------+-----------+-----------+------------+
	 * | 859649      | Foo       | Bar       | Foobar     |
	 * +-------------+-----------+-----------+------------+
	 * | 859650      | Fooy      | Barington | Sales      |
	 * +-------------+-----------+-----------+------------+
	 *
	 * And would return an array of return values (i.e. [true, true]).
	 *
	 * Description: bool crud::create( string $table, array $arr )
	 *
	 * @param string $string Name of table to insert values
	 * @param array  $arr    Associative array of keys and values to insert
	 *
	 * @return bool True if insertion successful else false
	 *
	 * @access public
	 * @since Method available since Release 1.0.0
	 */
	public function create ( $table, $arr ) {
		// Make sure a multidimentional array was not passed as $arr
		if ( array_filter( $arr, 'is_array' ) ){
			// If multidimentional then recurse
			foreach ( $arr as $row ) {
				$results[] = $this->create( $table, $row );
			}
			return $results;
		} else {
			$keys = array();
			$vals = array();
			$dt = array();
			$ph = array();
			$query;

			// Compile the query
			$mysql = $this->resource;
			$query = "INSERT INTO {$table}";

			foreach ( $arr as $key => $val ) {
				$keys[] = $key;

				// Escape any functions
				if ( preg_match( '/^function:\s*/i', $val ) ){
					$tmp = trim( preg_replace( '/^function:\s*/i', '', $val ) );
					$ph[] = $tmp;
				} else {
					$ph[] = '?';
					$vals[] = $val;

					// Get the data type of value
					if ( is_int( $val ) ) {
						$dt[] = 'i';
					} elseif ( is_double( $val ) ) {
						$dt[] = 'd';
					} elseif ( is_string( $val ) ) {
						$dt[] = 's';
					} elseif ( is_executable( $val ) || is_file( $val ) ) {
						$dt[] = 'b';
					}
				}
			}

			$tmp = implode( '`, `', $keys );
			$query .= " ( `{$tmp}` )";
			$tmp = implode( ', ', $ph );
			$query .= " VALUES ( {$tmp} )";

			if ( $stmt = $mysql->prepare( $query ) ) {
				$datatypes = implode( $dt );
				$args = array( $datatypes );
				for ( $i = 0; $i < count( $vals ); $i++ ) {
					$args [] = &$vals[$i];
				}

				// Bind parameters to prepared statement and execute
				call_user_func_array( array( $stmt, 'bind_param' ) , $args );
				return $stmt->execute();
			}
		}
		return false;
	}
	// }}}
	// {{{ delete

	/**
	 * Creates and executes a DELETE SQL query on a specified row of a database
	 *
	 * The id value submitted to the method is checked against the table's
	 * primary key. The primary key is optional and, if not passed, the method
	 * will attempt to find the primary key of the table.
	 *
	 * Description: int crud::delete( string $table, string $id [, string
	 *              $primaryKey] )
	 *
	 * @param string $table      Name of table to remove record from
	 * @param string $id         Id of record to remove
	 * @param string $primaryKey Name of column of table which contains the
	 *                           primary key
	 *
	 * @return int Id of row removed if successful else false
	 *
	 * @access public
	 * @since Method available since Release 1.0.0
	 */
	public function delete ( $table, $id, $primaryKey = '' ) {
		$mysql = $this->resource;
		// Find the primary key
		if ( empty( $primaryKey ) ) {
			if ( $pk = $this->read( "SHOW INDEX FROM {$table}" ) ) {
				foreach ( $pk as $row ) {
					foreach ( $row as $key => $val ) {
						if ( preg_match( '/^primary$/i', $row['Key_name'] ) ) {
							$primaryKey = $row['Column_name'];
						}
					}
				}
			}
		}

		// Prepare the query
		$query = "DELETE FROM {$table} WHERE {$primaryKey}=? LIMIT 1";
		if ( $stmt = $mysql->prepare( $query ) ) {
			$stmt->bind_param( 'i', $id );
			$stmt->execute();
			if ( $stmt->affected_rows > 0 ) {
				return $id; // return the row id if successfully deleted
			}
		}
		return false;
	}
	// }}}
	// {{{ read

	/**
	 * Creates and executes a SELECT query on a specified table of a
	 * database
	 *
	 * The query is performed on the current database and results are returned
	 * as a multidimensional array. The format for a returned row is as follows.
	 * The table:
	 *
	 * +-------------+-----------+-----------+------------+
	 * | employee_id | firstName | lastName  | department |
	 * +-------------+-----------+-----------+------------+
	 * | 859649      | Foo       | Bar       | Foobar     |
	 * +-------------+-----------+-----------+------------+
	 * | 859650      | Fooy      | Barington | Sales      |
	 * +-------------+-----------+-----------+------------+
	 *
	 * would yield:
	 *
	 * Array (
	 *     [0] => Array (
	 *         'firstName' => 'Foo',
	 *         'lastName' => 'Bar',
	 *         'department' => 'Foobar'
	 *     )
	 *     [1] => Array (
	 *         'firstName' => 'Fooy',
	 *         'lastName' => 'Barington',
	 *         'department' => 'Sales'
	 *     )
	 * )
	 *
	 * It should be noted (for traversing purposes) if only 1 row is requested
	 * the result will still be returned as a multidimensional array. If the
	 * query:
	 *
	 *     SELECT * FROM employees LIMIT 1
	 *
	 * were performed on the above table the resulting associative array would
	 * be:
	 *
	 * Array (
	 *     [0] => Array (
	 *         'firstName' => 'Foo',
	 *         'lastName' => 'Bar',
	 *         'department' => 'Foobar'
	 *     )
	 * )
	 *
	 * Description: array crud::read( string $query )
	 *
	 * @param string $query SQL query to submit to database
	 *
	 * @return array Associative array containing query results else false
	 *
	 * @access public
	 * @since Method available since Release 1.0.0
	 */
	public function read ( $query ) {
		$results = false; // Set flag to false in case something goes wrong

		$mysql = $this->resource;

		// Conditional is set up to filter out invalid queries/null stmt objects
		if ( $stmt = $mysql->prepare( $query ) ) {
			$stmt->execute();

			// Create local vars for method
			$meta = $stmt->result_metadata();
			$results = array();
			$parameters = array();

			// Build bind_result() array
			while ( $field = $meta->fetch_field() ) {
				$parameters[] = &$row[$field->name];
			}

			// Bind results to prepared statment with parameters
			call_user_func_array( array( $stmt, 'bind_result'), $parameters );

			// Collect the results
			while ( $stmt->fetch() ) {
				$tmp = array();
				foreach ( $row as $key => $val ) {
					$tmp[$key] = $val;
				}
				$results[] = $tmp;
			}
		}

		// Results false if failed else array
		return $results;
	}
	// }}}
	// {{{ update

	/**
	 * Creates and executes an UPDATE SQL query on a specified row of a database
	 *
	 * This method performs very similarly to the create method. The associative
	 * array submitted to this method should follow a naming convention of
	 * key => value where key is the column name of which the value would be
	 * updated:
	 *
	 * Array (
	 *    'firstName' => 'Foo',
	 *    'lastName' => 'Bar',
	 *    'department' => 'Foobar'
	 * )
	 *
	 * Would yield the following result (or something similar):
	 *
	 * +-------------+-----------+----------+------------+
	 * | employee_id | firstName | lastName | department |
	 * +-------------+-----------+----------+------------+
	 * | 859648      | Foo       | Bar      | Foobar     |
	 * +-------------+-----------+----------+------------+
	 *
	 * The method is designed to recursively process multidimensional arrays:
	 *
	 * Array (
	 *     [0] => Array (
	 *         'firstName' => 'Foo',
	 *         'lastName' => 'Bar',
	 *         'department' => 'Foobar'
	 *     )
	 *     [1] => Array (
	 *         'firstName' => 'Fooy',
	 *         'lastName' => 'Barington',
	 *         'department' => 'Sales'
	 *     )
	 * )
	 *
	 * Would yield:
	 *
	 * +-------------+-----------+-----------+------------+
	 * | employee_id | firstName | lastName  | department |
	 * +-------------+-----------+-----------+------------+
	 * | 859649      | Foo       | Bar       | Foobar     |
	 * +-------------+-----------+-----------+------------+
	 * | 859650      | Fooy      | Barington | Sales      |
	 * +-------------+-----------+-----------+------------+
	 *
	 * And would return an array of return values (i.e. [true, true]).
	 *
	 * Description: bool update( string $table, array $arr, string $id,
	 *              mixed $primaryKey = '' )
	 *
	 * @param string $table      Name of table to remove record from
	 * @param array  $arr        Associative array of keys and values to update
	 * @param string $id         Id of record to update
	 * @param string $primaryKey Name of column of table which contains the
	 *                           primary key
	 *
	 * @return int True if row updated successfully else false
	 *
	 * @access public
	 * @since Method available since Release 1.0.0
	 */
	public function update ( $table, $arr, $id, $primaryKey = '' ) {
		// Make sure a multidimentional array was not passed as $arr
		if ( array_filter( $arr, 'is_array' ) ){
			// If multidimentional then recurse
			foreach ( $arr as $row ) {
				$results[] = $this->create( $table, $row );
			}
			return $results;
		} else {
			$keys = array();
			$vals = array();
			$dt = array();
			$ph = array();
			$query;
			$primaryKey;

			// Compile the query
			$mysql = $this->resource;

			// Find the primary key
			if ( empty( $primaryKey ) ) {
				if ( $pk = $this->read( "SHOW INDEX FROM {$table}" ) ) {
					foreach ( $pk as $row ) {
						foreach ( $row as $key => $val ) {
							if ( preg_match( '/^primary$/i', $row['Key_name'] ) ) {
								$primaryKey = $row['Column_name'];
							}
						}
					}
				}
			}

			$query = "UPDATE {$table} SET";

			foreach ( $arr as $key => $val ) {
				$keys[] = $key;

				// Escape any functions
				if ( preg_match( '/^function:\s*/i', $val ) ){
					$tmp = trim( preg_replace( '/^function:\s*/i', '', $val ) );
					$ph[] = "{$key}={$tmp}";
				} else {
					$ph[] = "{$key}=?";
					$vals[] = $val;

					// Get the data type of value
					if ( is_int( $val ) ) {
						$dt[] = 'i';
					} elseif ( is_double( $val ) ) {
						$dt[] = 'd';
					} elseif ( is_string( $val ) ) {
						$dt[] = 's';
					} elseif ( is_executable( $val ) || is_file( $val ) ) {
						$dt[] = 'b';
					}
				}
			}

			$tmp = implode( ', ', $ph );
			$query .= " {$tmp}";
			$query .= " WHERE `{$primaryKey}` = '{$id}' LIMIT 1";

			if ( $stmt = $mysql->prepare( $query ) ) {
				$datatypes = implode( $dt );
				$args = array( $datatypes );
				for ( $i = 0; $i < count( $vals ); $i++ ) {
					$args [] = &$vals[$i];
				}

				// Bind parameters to prepared statement and execute
				call_user_func_array( array( $stmt, 'bind_param' ) , $args );
				return $stmt->execute();
			}
		}
		return false;
	}
	// }}}

}
// }}}

?>
