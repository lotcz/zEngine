<?php

require_once __DIR__ . '/../classes/model.php';

class CustomerModel extends zModel {

	public function loadByUserId($user_id) {
		$this->loadSingle('customer_user_id = ?', [$user_id], [PDO::PARAM_INT]);
	}

}
