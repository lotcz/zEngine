<?php

require_once __DIR__ . '/../classes/model.php';

class AdminModel extends zModel {

	public function loadByUserId(int $user_id) {
		$where = 'admin_user_id = ?';
		$bindings = [$user_id];
		$types =  [PDO::PARAM_INT];
		$this->loadSingle($where, $bindings, $types);
	}

}
