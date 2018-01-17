<?php

/**
* This class simplifies creating of mysql queries.
*/
class zSqlQuery {
	
	public $db = null;
	public $table_name = 'table';	
	public $query_type = 'select';
	public $where = null;
	public $whereSQL = '';
	public $bindings = null;
	public $types = null;
	public $orderby = null;
	public $orderbySQL = '';
	public $paging = null;
	public $limitSQL = '';
	public $setSQL = ''; // for UPDATE	
	public $valuesSQL = ''; // for INSERT
	
	function __construct($db, $table_name, $query_type = null) {
		$this->db = $db;		
		$this->table_name = $table_name;
		if (isset($query_type)) {
			$this->query_type = $query_type;
		}
	}
	
	static function phpDatetime($mysqldate) {		
		if (isset($mysqldate) && (strlen($mysqldate) > 0)) {
			return strtotime($mysqldate);
		} else {
			return null;
		}
	}
	
	static function mysqlDatetime($time = null) {
		if (!isset($time)) {
			return null;
		} else {
			return date('Y-m-d H:i:s', $time);
		}
	}
	
	static function mysqlTimestamp($time = null) {
		if (!isset($time)) {
			return null;
		} else {
			return date('Y-m-d H:i:s', $time);
		}
	}
	
	static function validateColumn($col) {
		return substr($col, 0, 50);
	}
	
	static function getTypeChar($val) {
		if (is_int($val)) {
			return 'i';	
		} else {
			return 's';
		}		
	}
	
	static function getExceptionMessage($operation, $query, $message) {
		return sprintf("zSqlQuery class issued an error during $operation query ($query): $message.");
	}
	
	static function executeSQL($db, $sql, $bindings = null, $types = null) {
		if ($statement = $db->prepare($sql)) {
			if (isset($bindings)) {
				$reset_types = false;
				if (!isset($types)) {
					$types = '';
					$reset_types = true;
				}
				$bindings_ref = [];
				foreach ($bindings as $key => $value) {
					$bindings_ref[] = & $bindings[$key];
					if ($reset_types) {
						$types .= zSqlQuery::getTypeChar($value);
					}
				}			
				array_unshift($bindings_ref, $types);
				call_user_func_array(array($statement, 'bind_param'), $bindings_ref);
			}
			if ($statement->execute()) {
				return $statement;
			} else {
				throw new Exception(Self::getExceptionMessage('execute', $sql, $db->error));
			}			
		} else {
			throw new Exception(Self::getExceptionMessage('prepare', $sql, $db->error));
		}
	}
	
	static function getRecordCount($db, $table_name, $whereSQL = '', $bindings = null, $types = null) {
		$count = null;
		$sql = sprintf('SELECT count(*) AS cnt FROM %s %s', $table_name, $whereSQL);
		$statement = Self::executeSQL($db, $sql, $bindings, $types);
		$result = $statement->get_result();
		if ($row = $result->fetch_assoc()) {
			$count = $row['cnt'];
		}
		$statement->close();
		return $count;
	}
	
	public function execute() {

		if (isset($this->orderby)) {
			$this->orderbySQL = sprintf('ORDER BY %s', $this->orderby);
		}
		
		if (isset($this->paging)) {
			$this->limitSQL = sprintf('LIMIT %d,%d', $this->paging->offset, $this->paging->limit);			
		}
		
		if (isset($this->where)) {			
			$this->whereSQL = sprintf('WHERE %s', $this->where);
		}
		
		if (isset($this->data)) {	
			$columns = [];
			$bindings = [];
			
			foreach ($this->data as $key => $value) {
				$columns[] = Self::validateColumn($key) . ' = ?';
				$bindings[] = & $value;						
			}
					
			// prepend bindings because SET comes before WHERE
			$this->bindings = array_merge($bindings, $this->bindings);
			
			$this->setSQL = sprintf('SET %s', implode(',', $columns));
		}
		
		switch ($this->query_type) {
			
			case 'update' :
				$sql = sprintf('UPDATE %s %s %s', $this->table_name, $this->setSQL, $this->whereSQL);
				break;
			
			case 'delete' :
				$sql = sprintf('DELETE FROM %s %s', $this->table_name, $this->whereSQL);
				break;
				
			case 'select':
			default:
				$sql = sprintf('SELECT * FROM %s %s %s %s', $this->table_name, $this->whereSQL, $this->orderbySQL, $this->limitSQL);
		
		}
		
		//REMOVE THIS AND PUT TO PAGING MODULE
		if (isset($this->paging) && !isset($this->paging->total_records)) {
			$this->paging->total_records = zSqlQuery::getRecordCount($this->db, $this->table_name, $this->whereSQL, $this->bindings, $this->types);
		}
		
		//dbg($sql);
		//var_dump($this->bindings);
		
		return zSqlQuery::executeSQL($this->db, $sql, $this->bindings, $this->types);
	}
	
	static function select($db, $table_name, $where = null, $bindings = null, $types = null, $paging = null, $orderby = null) {
		$query = new zSqlQuery($db, $table_name);
		$query->where = $where;
		$query->bindings = $bindings;
		$query->types = $types;
		$query->paging = $paging;
		$query->orderby = $orderby;
		return $query->execute();
	}
	
	static function selectToArray($db, $table_name, $where = null, $bindings = null, $types = null, $paging = null, $orderby = null) {		
		$list = [];
		$stmt = zSqlQuery::select($db, $table_name, $where, $bindings, $types, $paging, $orderby);
		if ($stmt) {
			$result = $stmt->get_result();
			while ($row = $result->fetch_assoc()) {				
				$list[] = $row;
			}
			$stmt->close();
		}
		return $list;
	}
	
	static function update($db, $table_name, $data, $where, $bindings = null) {
		$query = new zSqlQuery($db, $table_name, 'update');
		$query->where = $where;
		$query->bindings = $bindings;
		$query->data = $data;
		return $query->execute();
	}
	
	static function del($db, $table_name, $where, $bindings = null) {
		$query = new zSqlQuery($db, $table_name, 'delete');
		$query->where = $where;
		$query->bindings = $bindings;
		return $query->execute();
	}
	
}