<?php

class JET_DB {

	function __construct( string $table_name ) {
		$this->table = $table_name;
	}

	public function set_dh_host( $DB_HOST ) {
		$this->DB_HOST = $DB_HOST;
	}

	public function set_dh_user( $DB_USER ) {
		$this->DB_USER = $DB_USER;
	}

	public function set_dh_pass( $DB_PASSWORD ) {
		$this->DB_PASSWORD = $DB_PASSWORD;
	}

	public function set_dh_name( $DB_NAME ) {
		$this->DB_NAME = $DB_NAME;
	}

	public function set_dh_charset( $DB_CHARSET ) {
		$this->DB_CHARSET = $DB_CHARSET;
	}

	/*
	 * Connecting to database
	 *
	 * if you want to change parameters import variable value.
	 */
	public function DB_connection( $db_user = null, $db_pass = null, $db_name = null, $dh_charset = null, $db_host = null ) {
		isset( $db_user ) ?? $this->set_dh_host( $db_host );
		isset( $db_user ) ?? $this->set_dh_user( $db_user );
		isset( $db_pass ) ?? $this->set_dh_pass( $db_pass );
		isset( $db_name ) ?? $this->set_dh_name( $db_name );
		isset( $dh_charset ) ?? $this->set_dh_charset( $dh_charset );

		$this->connection = new mysqli( $this->DB_HOST, $this->DB_USER, $this->DB_PASSWORD, $this->DB_NAME );
		$this->connection->set_charset( $this->DB_CHARSET );

		if ( $this->connection->connect_error ) {
			die( "Database Connection failed!" );
		}

		return $this->connection;
	}

	/*
	 * using specific query
	 * @return first result from query
	 */
	public function query( $query ) {
		$this->connection = $this->DB_connection();
		$result           = $this->connection->query( $query );
		$this->connection->close();

		return $result;
	}

	/*
    * insert data in table
    */
	function insert( array $details ) {
		$this->connection = $this->DB_connection();

		$columns       = '';
		$values        = '';
		$details_count = count( $details );
		$i             = 0;

		foreach ( $details as $column => $value ) {
			$i ++;

			$columns .= ' ' . $column;
			$values  .= " '" . $value . "'";

			if ( $i < $details_count ) {
				$columns .= ',';
				$values  .= ',';
			}

		}

		$sql = "INSERT INTO  " . $this->table . "  ( {$columns} ) VALUES ({$values})";
		$this->connection->query( $sql );
		$result = $this->connection->insert_id;
		$this->connection->close();

		if ( $result == 0 ) {
			$result = false;
		}

		return $result;

	}

	/*
	* get count of table
	*/
	function count( array $where = null ) {
		$connection = $this->DB_connection();

		if ( $where ) {
			$where_query = $this->make_query( $where, 'where' );
		}

		$sql    = "SELECT count(1) FROM " . $this->table . " {$where_query} ";
		$result = $connection->query( $sql );
		$connection->close();

		if ( $result->num_rows > 0 ) {
			$result = $result->fetch_assoc()["count(1)"];

			if ( $result == 0 ) {
				$result = false;
			}

		} else {
			$result = false;
		}

		return $result;

	}

	/*
	 * update row
	 * $where is array of many column => value
	 * $details is array that want to update many column => value
	 */
	function update( array $where, array $details ) {
		$connection = $this->DB_connection();

		if ( $where ) {
			$where_query = $this->make_query( $where, 'where' );
		}

		$update = $this->make_query( $details, 'update' );

		$sql    = "UPDATE " . $this->table . " SET " . $update . $where_query;
		$result = $connection->query( $sql );
		$connection->close();

		return $result;
	}

	/*
	 * get row
	 * $where is column => value
	 */
	function get_row( array $where ) {
		$connection = $this->DB_connection();

		if ( $where ) {
			$where_query = $this->make_query( $where, 'where' );
		}

		$sql    = "SELECT * FROM " . $this->table . " {$where_query} ";
		$result = $connection->query( $sql );
		$connection->close();

		if ( $result->num_rows > 0 ) {
			$result = $result->fetch_assoc();
		} else {
			$result = false;
		}

		return $result;
	}

	/*
	 * get all
	 * $where is column => value
	 */
	function get_all( array $where ) {
		$connection = $this->DB_connection();

		if ( $where ) {
			$where_query = $this->make_query( $where, 'where' );
		}

		$sql    = "SELECT * FROM " . $this->table . " {$where_query} ";
		$result = $connection->query( $sql );
		$connection->close();

		if ( $result->num_rows > 0 ) {
			$result = $result->fetch_all( MYSQLI_ASSOC );
		} else {
			$result = false;
		}

		return $result;
	}

	//make some query
	private function make_query( array $parameters, string $set ): string {
		if ( $set == 'where' ) {
			$where_query = ' WHERE ';
			$i           = 0;

			foreach ( $parameters as $column => $value ) {
				$i ++;

				if ( $i != 1 ) {
					$where_query .= 'AND ';
				}

				$where_query .= $column . " = '" . $value . "' ";
			}

			return $where_query;
		} elseif ( $set == 'update' ) {
			$update = '';
			$i = 0;

			foreach ( $parameters as $column => $value ) {
				$i ++;
				$separator = ', ';

				if ( $i == 1 ) {
					$separator = '';
				}

				$update .= $separator . $column . ' = ' . "'" . $value . "' ";
			}

			return $update;
		}

		return '';
	}
	
	private $connection;
	private string
		$DB_HOST = JET_DB_HOST,
		$DB_USER = JET_DB_USER,
		$DB_PASSWORD = JET_DB_PASSWORD,
		$DB_NAME = JET_DB_NAME,
		$DB_CHARSET = JET_DB_CHARSET,
		$table;

}
