<?php

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

	function __construct(dbModule $db = null, int $id = null) {
		$this->db = $db;
		if (isset($id)) {
			$this->loadById($id);
		}
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
		return z::phpDatetime($this->val($key, $default));
	}

	public function loadSingle($where, $bindings = null, $types = null) {
		$sql = sprintf('SELECT * FROM %s WHERE %s', $this->table_name, $where);
		$statement = $this->db->executeQuery($sql, $bindings, $types);
		if ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
			$this->is_loaded = true;
			$this->setData($row);
		} else {
			$this->is_loaded = false;
			$this->data = [];
		}
		$statement->closeCursor();
	}

	public function loadById(int $id) {
		$where = sprintf('%s = ?', $this->id_name);
		$bindings = [$id];
		$types = [PDO::PARAM_INT];
		$this->loadSingle($where, $bindings, $types);
	}

	static function select($db, $table_name, $where = null, $orderby = null, $limit = null, $bindings = null, $types = null) {
		$statement = $db->executeSelectQuery($table_name, ['*'], $where, $orderby, $limit, $bindings, $types);
		$list = [];
		$class = get_called_class();
		while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
			$model = new $class($db);
			$model->setData($row);
			$model->is_loaded = true;
			$list[] = $model;
		}
		$statement->closeCursor();
		return $list;
	}

	public function save() {
		$id = $this->ival($this->id_name);

		$columns = [];
		$bindings = [];
		$types = [];

		foreach ($this->data as $key => $value) {
			if ($key != $this->id_name) {
				$columns[] = $key;
				$bindings[] = $value;
				$types[] = z::getDbType($value);
			}
		}

		if (isset($id) && $id > 0) {
			$bindings[] = $id;
			$types[] = PDO::PARAM_INT;

			$this->db->executeUpdateQuery($this->table_name, $columns, sprintf('%s = ?', $this->id_name), $bindings, $types);
		} else {
			$statement = $this->db->executeInsertQuery($this->table_name, $columns, $bindings, $types);
			$this->set($this->id_name, $this->db->lastInsertId());
		}

		return true;
	}

	public function delete(int $id = null) {
		if (!isset($id)) {
			$id = $this->ival($this->id_name);
		}
		return $this->db->executeDeleteQuery($this->table_name, sprintf('%s = ?', $this->id_name), [$id], [PDO::PARAM_INT]);
	}

	static function deleteById(dbModule $db, int $id) {
		$class = get_called_class();
		$m = new $class($db);
		return $m->delete($id);
	}

	public function loadAll($class) {
		if (isset(Self::$cache[$class])) {
			return Self::$cache[$class];
		} else {
			$all = Self::select($this->db, $this->table_name);
			Self::$cache[$class] = $all;
		}
		return Self::$cache[$class];
	}

	static function all(dbModule $db) {
		$class = get_called_class();
		$m = new $class($db);
		return $m->loadAll($class);
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

	/**
	* Will accept array of models, column name and type.
	* Returns array of values of selected column.
	*/
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
