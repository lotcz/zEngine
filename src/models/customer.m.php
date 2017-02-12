<?php

class CustomerModel extends zModel {
	
	public $table_name = 'customers';
	public $id_name = 'customer_id';

	public function loadByEmail($email) {
		$where = 'customer_email = ?';
		$bindings = [$email];
		$types = 's';
		$this->loadSingleFiltered($where, $bindings, $types);		
	}
	
}