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

	public function executeQuery($sql, $bindings = null, $types = null) {
		$stmt = $this->getConnection()->prepare($sql);
		if (isset($bindings) && sizeof($bindings) > 0) {
			for($i = 0, $max = sizeof($bindings); $i < $max; $i++) {
				$stmt->bindValue($i+1, $bindings[$i], $types[$i]);
			}
		}
	 	$stmt->execute();
		return $stmt;
	}

	public function executeSelectQuery($table_name, $columns = '*', $where = null, $orderby = null, $limit = null, $bindings = null, $types = null) {
		$whereSQL = '';
		if (isset($where)) {
			$whereSQL = sprintf('WHERE %s', $this->where);
		}

		$orderbySQL = '';
		if (isset($orderby)) {
			$orderbySQL = sprintf('ORDER BY %s', $this->orderby);
		}

		$limitSQL = '';
		if (isset($limit)) {
			$limitSQL = sprintf('LIMIT %s', $limit);
		}

		$sql = sprintf('SELECT %s FROM %s %s %s %s', $columns, $table_name, $whereSQL, $orderbySQL, $limitSQL);
		return $this->executeQuery($sql, $bindings, $types);
	}

	public function executeUpdateQuery($table_name, $columns, $where, $bindings, $types) {
		$whereSQL = sprintf('WHERE %s', $where);

		$columnsSQL = [];
		foreach ($columns as $column) {
			$columnsSQL[] = $column . ' = ?';
		}

		$sql = sprintf('UPDATE %s SET %s %s', $table_name, implode(',', $columnsSQL), $whereSQL);
		return $this->executeQuery($sql, $bindings, $types);
	}

	public function executeInsertQuery($table_name, $columns, $bindings, $types) {
		$values = [];

		foreach ($columns as $column) {
			$values[] = '?';
		}

		$sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', $table_name, implode(',', $columns), implode(',', $values));
		return $this->executeQuery($sql, $bindings, $types);
	}

	public function lastInsertId() {
		return $this->getConnection()->lastInsertId();
	}

	public function executeDeleteQuery($table_name, $where = null, $bindings = null, $types = null) {
		$whereSQL = '';
		if (isset($where)) {
			$whereSQL = sprintf('WHERE %s', $this->where);
		}

		$sql = sprintf('DELETE FROM %s %s', $table_name, $whereSQL);
		return $this->executeQuery($sql, $bindings, $types);
	}

	public function getRecordCount($table_name, $whereSQL = '', $bindings = null, $types = null) {
		$count = null;
		$sql = sprintf('SELECT count(*) AS cnt FROM %s %s', $table_name, $whereSQL);
		$statement = $this->executeQuery($sql, $bindings, $types);
		if ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
			$count = $row['cnt'];
		}
		$statement->closeCursor();
		return $count;
	}

	public function executeFile($file_path, $db_user = null, $password = null, $db_name = null) {
		$hostname = $this->config['hostname'];
		if (!isset($db_user)) {
			$db_user = $this->config['user'];
		}
		if (!isset($password)) {
			$password = $this->config['password'];
		}
		if (!isset($db_name)) {
			$db_name = $this->config['database'];
		}

		$command = "mysql --default-character-set=utf8 -h $hostname -D $db_name --user=$db_user --password='$password' < ";

		$output = shell_exec($command . $file_path);
		echo $output;
	}
}
