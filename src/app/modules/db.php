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

	/**
	* a PDO connection type
	*/
	public $connection_type = null;

	public function onEnabled() {
		$this->requireConfig();
		$this->connection_type = $this->getConfigValue('connection_type', 'mysql');
	}

	/**
	* @return PDO
	*/
	private function getConnection() {
		if (!isset($this->connection)) {
			if ($this->connection_type == 'sqlite') {
				$connection_string = sprintf('sqlite:%s', $this->getConfigValue('hostname'));
			} else {
				$connection_string = sprintf('%s:host=%s;dbname=%s;charset=%s',
					$this->connection_type,
					$this->getConfigValue('hostname'),
					$this->getConfigValue('database'),
					$this->getConfigValue('charset', 'UTF8')
				);
			}
			$this->connection = new PDO(
				$connection_string,
				$this->getConfigValue('user'),
				$this->getConfigValue('password'),
				$this->getConfigValue('options', [])
			);
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			$this->connection->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
		}
		return $this->connection;
	}

	public function onBeforeRender() {
		// reset connection before page rendering
		$this->connection = null;
	}

	/**
	* Executes sql query without using prepare - dangerous
	* @param String $sql Sql query to execute.
	* @return null
	*/
	public function executeQueryUnprepared($sql) {
		$connection = $this->getConnection();
		$connection->exec($sql);
	}

	/**
	* Executes sql query.
	* @param String $sql Sql query to execute. Replace "?" with values of bound.
	* @param Array $bindings Array of values to be subject to binding.
	* @param Array $types Array of PDO type specifications for binding values.
	* @return PDOStatement
	*/
	public function executeQuery(string $sql, array|null $bindings = null, array|null $types = null) {
		if ($this->z->isDebugMode()) {
			$values = sprintf('[%s]', empty($bindings) ? '' : implode(", ", $bindings));
			$this->z->errorlog->write(
				sprintf(
					"Running query: %s\r\nValues: %s",
					$sql,
					$values
				)
			);
		}

		$connection = $this->getConnection();
		$stmt = $connection->prepare($sql);
		if (!$stmt) {
			throw new Exception(sprintf('Unknown error in query: %s.', $sql));
		}
		if (isset($bindings) && sizeof($bindings) > 0) {
			for($i = 0, $max = sizeof($bindings); $i < $max; $i++) {
				$stmt->bindValue($i+1, $bindings[$i], $types[$i]);
			}
		}

		try {
			$result = $stmt->execute();
			if ($result) {
				return $stmt;
			} else {
				$info = $stmt->errorInfo();
				$code = $info[1];
				$desc = $info[2];
				throw new Exception(sprintf('Error %s - %s', $code, $desc));
			}
		} catch (Exception $e) {
			$exceptionMessage = $e->getMessage();
			$shortMessage =  sprintf(
				"Error in query: %s\r\n%s",
				$sql,
				$exceptionMessage
			);
			$values = implode(", ", $bindings);
			$typenames = implode(", ", $types);
			$fullMessage = sprintf(
				"Error in query: %s\r\nValues: %s\r\nTypes: %s\r\n%s",
				$sql,
				$values,
				$typenames,
				$exceptionMessage
			);
			$this->z->errorlog->write($fullMessage);
			throw new Exception(
				sprintf(
					"%s\r\nSee zEngine error log for more details.",
					$shortMessage
				),
				500,
				$e
			);
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

		$sql = sprintf('SELECT %s FROM `%s` %s %s %s', implode(',', $columns), $table_name, $whereSQL, $orderbySQL, $limitSQL);
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

		$sql = sprintf('UPDATE `%s` SET %s %s', $table_name, implode(',', $columnsSQL), $whereSQL);
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

		$sql = sprintf('INSERT INTO `%s` (%s) VALUES (%s)', $table_name, implode(',', $columns), implode(',', $values));
		return $this->executeQuery($sql, $bindings, $types);
	}

	/**
	 * Executes insert query.
	 * @param String $procedure_name Name of the procedure.
	 * @param Array $parameters Array of values to be subject to parameter binding.
	 * @param Array $types Array of PDO type specifications for binding values.
	 * @return PDOStatement
	 */
	public function executeStoredProcedure($procedure_name, $parameters, $types)
	{
		return $this->executeQuery(sprintf('call %s(?);', $procedure_name), $parameters, $types);
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

		$sql = sprintf('DELETE FROM `%s` %s', $table_name, $whereSQL);
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
	* Returns median value
	* @param String $table_name Name of the table.
	* @param String $field_name Name of the field to calculate median of. Must be numeric.
	* @param String $where WHERE part of sql query with ? placeholders.
	* @param Array $bindings Array of values to be subject to binding.
	* @param Array $types Array of PDO type specifications for binding values.
	* @return float
	*/
	public function getMedianValue($table_name, $field_name, $where = null, $bindings = null, $types = null) {
		$count = $this->getRecordCount($table_name, $where, $bindings, $types);
		if ($count > 0) {
			$field = "$field_name AS median";
			if ($count % 2 == 0) {
				$offset = ($count / 2) - 1;
				$limit = 2;
			} else {
				$offset = floor($count / 2);
				$limit = 1;
			}
			$limit_sql = "$offset, $limit";
			$sum = 0;
			$statement = $this->executeSelectQuery($table_name, [$field], $where, $field_name, $limit_sql, $bindings, $types);
			while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
				$sum += z::parseFloat($row['median']);
			}
			$statement->closeCursor();
			return $sum / $limit;
		} else {
			return 0;
		}
	}

	/**
	* Executes sql file.
	* Mysql will be executed through command line and returns output.
	* For other db types file will be executed statement by statement. Separate statements by semicolon and new line (";\n")
	* @return string
	*/
	public function executeFile($file_path, $db_user = null, $password = null, $db_name = null) {
		if ($this->connection_type == 'mysql') {
			$hostname = $this->config['hostname'];
			$db_user = $db_user ?? $this->config['user'];
			$password = $password ?? $this->config['password'];
			$db_name = $db_name ?? $this->config['database'];
			$command = "mysql --default-character-set=utf8 -h $hostname -D $db_name --user=$db_user --password=$password < $file_path";
			return shell_exec($command);
		} else {
			$sql = file_get_contents($file_path);
			$statements = explode(';\n', $sql);
			foreach ($statements as $statement) {
				if (strlen($statement) > 2) {
					$this->executeQueryUnprepared($statement);
				}
			}
			return 'OK';
		}
	}

}
