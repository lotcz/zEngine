<?php

require_once __DIR__ . '/../classes/model.php';

/**
* Module that handles connection to database.
*/
class dbModule extends zModule {

	/**
	* a PDO connection
	*/
	private $connection = null;

	public function onEnabled() {
		$this->requireConfig();
	}

	/**
	* @return PDO
	*/
	private function getConnection() {
		if (!isset($this->connection)) {
			$this->connection = new PDO(
				sprintf('%s:host=%s;dbname=%s;charset=%s',
					$this->getConfigValue('connection_type', 'mysql'),
					$this->getConfigValue('hostname'),
					$this->getConfigValue('database'),
					$this->getConfigValue('charset', 'UTF8')
				),
				$this->getConfigValue('user'),
				$this->getConfigValue('password'),
				$this->getConfigValue('options', [])
			);
		}
		return $this->connection;
	}

	public function onBeforeRender() {
		// reset connection before page rendering
		$this->connection = null;
	}

	/**
	* Executes sql query.
	* @param String $sql Sql query to execute. Replace binded variables with "?".
	* @param Array $bindings Array of values to be subject to binding.
	* @param Array $types Array of PDO type specifications for binding values.
	* @return PDOStatement
	*/
	public function executeQuery($sql, $bindings = null, $types = null) {
		$connection = $this->getConnection();
		$stmt = $connection->prepare($sql);
		if (isset($bindings) && sizeof($bindings) > 0) {
			for($i = 0, $max = sizeof($bindings); $i < $max; $i++) {
				$stmt->bindValue($i+1, $bindings[$i], $types[$i]);
			}
		}
	 	if ($stmt->execute()) {
			return $stmt;
		} else {
			$info = $stmt->errorInfo();
			$code = $info[1];
			$desc = $info[2];
			throw new Exception(sprintf('Error %s - %s in query: %s.', $code, $desc, $sql));
		}
	}

	/**
	* Executes select query.
	* @param String $table_name Name of the table.
	* @param Array $columns Array of column names to be selected.
	* @param String $where WHERE part of sql query with ? placeholders.
	* @param String $ordery ORDER BY part of sql query. Comma separated list of columns.
	* @param String $limit LIMIT part of sql query.
	* @param Array $bindings Array of values to be subject to binding.
	* @param Array $types Array of PDO type specifications for binding values.
	* @return PDOStatement
	*/
	public function executeSelectQuery($table_name, $columns = ['*'], $where = null, $orderby = null, $limit = null, $bindings = null, $types = null) {
		$whereSQL = '';
		if (isset($where)) {
			$whereSQL = sprintf('WHERE %s', $where);
		}

		$orderbySQL = '';
		if (isset($orderby)) {
			$orderbySQL = sprintf('ORDER BY %s', $orderby);
		}

		$limitSQL = '';
		if (isset($limit)) {
			$limitSQL = sprintf('LIMIT %s', $limit);
		}

		$sql = sprintf('SELECT %s FROM %s %s %s %s', implode(',', $columns), $table_name, $whereSQL, $orderbySQL, $limitSQL);
		return $this->executeQuery($sql, $bindings, $types);
	}

	/**
	* Executes update query.
	* @param String $table_name Name of the table.
	* @param Array $columns Array of column names to be selected.
	* @param String $where WHERE part of sql query with ? placeholders.
	* @param Array $bindings Array of values to be subject to binding.
	* @param Array $types Array of PDO type specifications for binding values.
	* @return PDOStatement
	*/
	public function executeUpdateQuery($table_name, $columns, $where, $bindings, $types) {
		$whereSQL = sprintf('WHERE %s', $where);

		$columnsSQL = [];
		foreach ($columns as $column) {
			$columnsSQL[] = $column . ' = ?';
		}

		$sql = sprintf('UPDATE %s SET %s %s', $table_name, implode(',', $columnsSQL), $whereSQL);
		return $this->executeQuery($sql, $bindings, $types);
	}

	/**
	* Executes insert query.
	* @param String $table_name Name of the table.
	* @param Array $columns Array of column names to be selected.
	* @param Array $bindings Array of values to be subject to binding.
	* @param Array $types Array of PDO type specifications for binding values.
	* @return PDOStatement
	*/
	public function executeInsertQuery($table_name, $columns, $bindings, $types) {
		$values = [];

		foreach ($columns as $column) {
			$values[] = '?';
		}

		$sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', $table_name, implode(',', $columns), implode(',', $values));
		return $this->executeQuery($sql, $bindings, $types);
	}

	/**
	* @return string
	*/
	public function lastInsertId() {
		return $this->getConnection()->lastInsertId();
	}

	/**
	* Executes delete query.
	* @param String $table_name Name of the table.
	* @param String $where WHERE part of sql query with ? placeholders.
	* @param Array $bindings Array of values to be subject to binding.
	* @param Array $types Array of PDO type specifications for binding values.
	* @return PDOStatement
	*/
	public function executeDeleteQuery($table_name, $where = null, $bindings = null, $types = null) {
		$whereSQL = '';
		if (isset($where)) {
			$whereSQL = sprintf('WHERE %s', $where);
		}

		$sql = sprintf('DELETE FROM %s %s', $table_name, $whereSQL);
		return $this->executeQuery($sql, $bindings, $types);
	}

	/**
	*
	* @return int
	*/
	public function getRecordCount($table_name, $where = null, $bindings = null, $types = null) {
		$count = null;
		$statement = $this->executeSelectQuery($table_name, ['count(*) as cnt'], $where, null, null, $bindings, $types);
		if ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
			$count = z::parseInt($row['cnt']);
		}
		$statement->closeCursor();
		return $count;
	}

	/**
	* Executes sql file through command line and returns output.
	* @return string
	*/
	public function executeFile($file_path, $db_user = null, $password = null, $db_name = null) {
		$hostname = $this->config['hostname'];
		$db_user = $db_user ?? $this->config['user'];
		$password = $password ?? $this->config['password'];
		$db_name = $db_name ?? $this->config['database'];
		$command = "mysql --default-character-set=utf8 -h $hostname -D $db_name --user=$db_user --password=$password < $file_path";
		return shell_exec($command);
	}

}
