<?php

require_once __DIR__ . '/../classes/query.php';
require_once __DIR__ . '/../classes/paging.php';
require_once __DIR__ . '/../classes/model.php';

/**
* Module that handles connecting to mysql databases.
*/
class mysqlModule extends zModule {

	public $connection = null;

	public function onEnabled() {
		$this->connection = new mysqli($this->config['db_host'], $this->config['db_login'], $this->config['db_password'], $this->config['db_name']);
		if ($this->connection->connect_errno > 0) {
			throw new Exception('Database connection error: ' . $this->connection->connect_error);
		}
		$this->connection->set_charset('utf8');
		$this->z->core->db = $this->connection;
	}

	public function onBeforeRender() {
		$this->connection->close();
	}
}
