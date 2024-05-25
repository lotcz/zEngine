<?php

/**
* Base class for all zEngine models.
*/
class zModel {

	protected $db = null;

	static $table_names = [];
	static $id_names = [];
	static $cache = [];

	public $table_name = null;
	public $id_name = null;

	public $is_loaded = false;
	public $data = [];

	function __construct(dbModule $db = null, int $id = null) {
		$this->db = $db;
		$class_name = get_called_class();
		if (!(isset(zModel::$table_names[$class_name])) && isset($this->table_name)) {
			zModel::$table_names[$class_name] = $this->table_name;
		}
		if (!(isset(zModel::$id_names[$class_name])) && isset($this->id_name)) {
			zModel::$id_names[$class_name] = $this->id_name;
		}
		if (isset($id)) {
			$this->loadById($id);
		}
	}

	static function getTableName() : string {
		$class_name = get_called_class();
		if (!(isset(zModel::$table_names[$class_name]))) {
			$dummy = new $class_name();
			if (!(isset(zModel::$table_names[$class_name]))) {
				zModel::$table_names[$class_name] = strtolower(substr($class_name, 0, strlen($class_name) - 5));
			}
		}
		return zModel::$table_names[$class_name];
	}

	static function getIdName() : string {
		$class_name = get_called_class();
		if (!(isset(zModel::$id_names[$class_name]))) {
			$dummy = new $class_name();
			if (!(isset(zModel::$id_names[$class_name]))) {
				zModel::$id_names[$class_name] = $class_name::getTableName() . '_id';
			}
		}
		return zModel::$id_names[$class_name];
	}

	public function setData($data, $only_update = false) {
		foreach ($data as $key => $value) {
			if (isset($this->data[$key]) or !$only_update) {
				$this->data[$key] = $value;
			}
		}
	}

	/** Column names will be stripped of table names to be shorter.
	* Useful when extracting data for json.
	* @return Array associative array of columns
	*/
	public function getStrippedData() {
		$table_name = $this->getTableName();
		$strip_chars = strlen($table_name) + 1;
		$result = [];
		foreach ($this->data as $key => $value) {
			if (z::startsWith($key, $table_name)) {
				$result[substr($key, $strip_chars)] = $this->get($key);
			} else {
				$result[$key] = $this->get($key);
			}
		}
		return $result;
	}

	/** Extracting data for json.
	* @return Array associative array of columns
	*/
	public function getJson() {
		return (Object) $this->getStrippedData();
	}

	/** Column names will be prefixed by table names.
	* This is reverse to getStrippedData
	*/
	public function setFromStrippedData($data) {
		if (isset($data)) {
			$table_name = $this->getTableName();
			foreach ($data as $key => $value) {
				$this->set($table_name . '_' . $key, $value);
			}
		}
	}

	/**
	* This is reverse to getJson
	*/
	public function setFromJson($json) {
		$this->setFromStrippedData($json);
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

	public function get($key, $default = null) {
		return $this->val($key, $default);
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
		$sql = sprintf('SELECT * FROM %s WHERE %s', get_called_class()::getTableName(), $where);
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
		$where = sprintf('%s = ?', get_called_class()::getIdName());
		$bindings = [$id];
		$types = [PDO::PARAM_INT];
		$this->loadSingle($where, $bindings, $types);
	}

	/**
	* Executes select query and returns array of zModel.
	* @return Array
	*/
	static function select($db, $table_name, $where = null, $orderby = null, $limit = null, $bindings = null, $types = null) {
		$statement = $db->executeSelectQuery($table_name, ['*'], $where, $orderby, $limit, $bindings, $types);
		$list = [];
		$class_name = get_called_class();
		while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
			$model = new $class_name($db);
			$model->setData($row);
			$model->is_loaded = true;
			$list[] = $model;
		}
		$statement->closeCursor();
		return $list;
	}

	static function selectSingle($db, $table_name, $where = null, $orderby = null, $limit = null, $bindings = null, $types = null) {
		$result = zModel::select($db, $table_name, $where, $orderby, $limit, $bindings, $types);
		if (count($result) > 0) {
			return $result[0];
		} else {
			return null;
		}
	}

	public function getId() {
		$class_name = get_called_class();
		return $this->ival($class_name::getIdName());
	}

	public function save() {
		$class_name = get_called_class();
		$id = $this->ival($class_name::getIdName());

		$columns = [];
		$bindings = [];
		$types = [];

		foreach ($this->data as $key => $value) {
			if ($key != $class_name::getIdName()) {
				$columns[] = $key;
				$bindings[] = $value;
				$types[] = z::getDbType($value);
			}
		}

		if (isset($id) && $id > 0) {
			$bindings[] = $id;
			$types[] = PDO::PARAM_INT;

			$this->db->executeUpdateQuery($class_name::getTableName(), $columns, sprintf('%s = ?', $class_name::getIdName()), $bindings, $types);
		} else {
			$statement = $this->db->executeInsertQuery($class_name::getTableName(), $columns, $bindings, $types);
			$this->set($class_name::getIdName(), $this->db->lastInsertId());
		}

		return true;
	}

	public function updateMultiReference($table, $ref_id_field, $other_ref_id_field, $values) {
		$id = $this->getId();
		$this->db->executeDeleteQuery($table, sprintf('%s = ?', $ref_id_field), [$id], [PDO::PARAM_INT]);
		foreach ($values as $value) {
			$val = z::parseInt($value);
			$this->db->executeInsertQuery($table, [$ref_id_field, $other_ref_id_field], [$id, $val], [PDO::PARAM_INT, PDO::PARAM_INT]);
		}
	}

	public function delete(int $id = null) {
		$class_name = get_called_class();
		if (!isset($id)) {
			$id = $this->ival($class_name::getIdName());
		}
		return $this->db->executeDeleteQuery($class_name::getTableName(), sprintf('%s = ?', $class_name::getIdName()), [$id], [PDO::PARAM_INT]);
	}

	public function clone() {
		$class = get_called_class();
		$m = new $class($this->db);
		$id_name = $class::getIdName();
		foreach ($this->data as $key => $value) {
			if ($key != $id_name) {
				$m->set($key, $this->get($key));
			}
		}
		return $m;
	}

	static function deleteById(dbModule $db, int $id) {
		$class = get_called_class();
		$m = new $class($db);
		return $m->delete($id);
	}

	/**
	* Loads whole table and return it as an array of models.
	* @return Array
	*/
	static function all(dbModule $db) {
		$class_name = get_called_class();
		if (!isset(zModel::$cache[$class_name])) {
			zModel::$cache[$class_name] = $class_name::select($db, $class_name::getTableName());
		}
		return zModel::$cache[$class_name];
	}

	/* static methods for working with arrays of models */

	/**
	* Return Json array
	* @return Object
	*/
	static function toJson($arr) {
		$result = [];
		foreach ($arr as $model) {
			$result[] = $model->getJson();
		}
		return $result;
	}

	/**
	* Find an element matching the filter.
	* @return zModel
	*/
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

	/**
	* Return sum of values in a single column.
	* @return Number
	*/
	static function sum($arr, $field) {
		$sum = 0;
		foreach ($arr as $model) {
			$sum += $model->fval($field, 0);
		}
		return $sum;
	}

	/**
	* Sort array of model entities by column.
	* @return Array New sorted array
	*/
	static function sort($arr, $field) {
		$result = [];
		$min = null;
		for ($i = 0, $max = count($arr); $i < $max; $i++) {
			for ($i2 = $i, $max2 = count($arr); $i2 < $max2; $i2++) {
				if ($min === null || $min->val($field) > $arr[$i2]->val($field)) {
					$min = $arr[$i2];
				}
			}
			$result[] = $min;
			$min = null;
		}
		return $result;
	}

	/**
	* Will accept array of models, column name and type.
	* @return Array array of values from selected column.
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

	/**
	* Will accept array of models and return array of it's data.
	* Column names will be stripped of table names to be shorter.
	* Useful when extracting data for json.
	* @return Array array of data components
	*/
	static function extractDataFromArray($arr) {
		$result = [];
		foreach ($arr as $model) {
			$result[] = $model->getStrippedData();
		}
		return $result;
	}

}
