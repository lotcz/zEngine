<?php

require_once __DIR__ . '/query.php';

/**
* Base class for all zEngine models.
*/
class zModel {

	protected $db = null;

	public $table_name = 'table';
	public $id_name = 'table_id';

	static $cache = [];
	public $is_loaded = false;
	public $data = [];

	function __construct($db = null, $id = null) {
		$this->db = $db;
		if (isset($id)) {
			$this->loadById($id);
		}
	}

	static function getExceptionMessage($table, $operation, $query, $message) {
		return sprintf("zModel class for table $table issued an error during $operation query ($query): $message.");
	}

	public function setData($data, $only_update = false) {
		foreach ($data as $key => $value) {
			if (isset($this->data[$key]) or !$only_update) {
				$this->data[$key] = $value;
			}
		}
	}

	public function set($key, $value) {
		$this->data[$key] = $value;
	}

	public function val($key, $default = null) {
		if (isset($this->data[$key])) {
			return $this->data[$key];
		} else {
			return $default;
		}
	}

	public function ival($key, $default = null) {
		return z::parseInt($this->val($key, $default));
	}

	public function fval($key, $default = null) {
		return floatval($this->val($key, $default));
	}

	public function bval($key, $default = false) {
		return boolval($this->ival($key, $default));
	}
	
	public function dtval($key, $default = null) {
		return zSqlQuery::phpDatetime($this->val($key, $default));
	}

	public function loadSingleFiltered($where, $bindings = null, $types = null) {
		$statement = zSqlQuery::select($this->db, $this->table_name, $where, $bindings, $types);
		if ($statement) {
			$result = $statement->get_result();
			if ($row = $result->fetch_array(MYSQLI_ASSOC)) {
				$this->is_loaded = true;
				$this->setData($row);
			} else {
				$this->is_loaded = false;
				$this->data = [];
			}
			$statement->close();
		}
	}

	public function loadById($id) {
		$id = z::parseInt($id);
		if (isset($id)) {
			$where = sprintf('%s = ?', $this->id_name);
			$bindings = [$id];
			$this->loadSingleFiltered($where, $bindings);
		}
	}

	static function select($db, $table_name, $where = null, $bindings = null, $types = null, $paging = null, $orderby = null) {
		$list = [];
		$stmt = zSqlQuery::select($db, $table_name, $where, $bindings, $types, $paging, $orderby);
		if ($stmt) {
			$result = $stmt->get_result();
			$class = get_called_class();
			while ($row = $result->fetch_assoc()) {
				$model = new $class($db);
				$model->setData($row);
				$model->is_loaded = true;
				$list[] = $model;
			}
			$stmt->close();
		}
		return $list;
	}

	public function fetch() {
		return $this->result->fetch_assoc();
	}

	public function close() {
		return $this->stmt->close();
	}

	public function save() {
		$result = false;
		$id = $this->ival($this->id_name);

		if (isset($id) && $id > 0) {
			$columns = [];
			$bindings = [];
			$types = '';

			foreach ($this->data as $key => $value) {
				if ($key != $this->id_name) {
					$columns[] = zSqlQuery::validateColumn($key) . ' = ?';
					$bindings[] = & $this->data[$key];
					$types .= zSqlQuery::getTypeChar($value);
				}
			}
			$bindings[] = & $this->data[$this->id_name];
			$types .= 'i';
			array_unshift($bindings, $types);
			$sql = sprintf('UPDATE %s SET %s WHERE %s = ?', $this->table_name, implode(',', $columns), $this->id_name);

			if ($st = $this->db->prepare($sql)) {
				call_user_func_array(array($st, 'bind_param'), $bindings);
				if ($st->execute()) {
					$result = true;
				} else {
					throw new Exception(Self::getExceptionMessage($this->table_name, 'execute', $sql, $this->db->error));
				}
				$st->close();
			} else {
				throw new Exception(Self::getExceptionMessage($this->table_name, 'prepare', $sql, $this->db->error));
			}
		} else {
			$columns = [];
			$values = [];
			$bindings = [];
			$types = '';

			foreach ($this->data as $key => $value) {
				if ($key != $this->id_name) {
					$columns[] = zSqlQuery::validateColumn($key);
					$values[] = '?';
					$bindings[] = & $this->data[$key];
					$types .= zSqlQuery::getTypeChar($value);
				}
			}
			array_unshift($bindings, $types);
			$sql = sprintf('INSERT INTO %s (%s) VALUES (%s)', $this->table_name, implode(',', $columns), implode(',', $values));
					
			if ($st = $this->db->prepare($sql)) {
				//echo $sql;
				call_user_func_array(array($st, 'bind_param'), $bindings);
				if ($st->execute()) {
					$this->is_loaded = true;
					$result = true;
					$this->data[$this->id_name] = $this->db->insert_id;
				} else {
					throw new Exception(Self::getExceptionMessage($this->table_name, 'execute', $sql, $this->db->error));
				}
				$st->close();
			} else {
				throw new Exception(Self::getExceptionMessage($this->table_name, 'prepare', $sql, $this->db->error));
			}
		}
		return $result;
	}

	public function deleteById($id = null) {
		if (!isset($id)) {
			$id = $this->val($this->id_name);
		}
		$sql = sprintf('DELETE FROM %s WHERE %s = ?', $this->table_name, $this->id_name);
		if ($statement = $this->db->prepare($sql)) {
			$statement->bind_param('i', $id);
			if ($statement->execute()) {
				$statement->close();
				$this->is_loaded = false;
				$this->data = [];
				return true;
			} else {
				throw new Exception(Self::getExceptionMessage($this->table_name, 'execute', $sql, $this->db->error));
			}
		} else {
			throw new Exception(Self::getExceptionMessage($this->table_name, 'prepare', $sql, $this->db->error));
		}
	}

	static function del($db, $id) {
		$class = get_called_class();
		$m = new $class($db);
		return $m->deleteById($id);
	}

	public function getAll($class) {
		if (isset(Self::$cache[$class])) {
			return Self::$cache[$class];
		} else {
			$all = Self::select($this->db, $this->table_name);
			Self::$cache[$class] = $all;
		}
		return Self::$cache[$class];
	}

	static function all($db) {
		$class = get_called_class();
		$m = new $class($db);
		return $m->getAll($class);
	}

	/* static methods for working with arrays of models */

	static function find($arr, $field, $value) {
		if (isset($arr) && count($arr) > 0) {
			foreach ($arr as $model) {
				if ($model->val($field) == $value) {
					return $model;
				}
			}
			return null;
		} else {
			return null;
		}
	}

	static function sum($arr, $field) {
		$sum = 0;
		foreach ($arr as $model) {
			$sum += $model->fval($field, 0);
		}
		return $sum;
	}
	
	static function columnAsArray($arr, $field, $type = 's') {
		$result = [];
		foreach ($arr as $model) {
			if ($type == 'i') {
				$result[] = $model->ival($field);
			} elseif ($type == 'f') {
				$result[] = $model->fval($field);
			} else {
				$result[] = $model->val($field);
			}
		}
		return $result;
	}

}
