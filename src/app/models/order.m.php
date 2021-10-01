<?php

require_once __DIR__ . '/../classes/model.php';
require_once __DIR__ . '/order_state.m.php';

class OrderModel extends zModel {

	function loadUnfinishedByCustomer(int $customer_id) {
		$this->loadSingle('order_customer_id = ? and order_order_state_id = ?', [$customer_id, OrderStateModel::STATE_NEW], [PDO::PARAM_INT, PDO::PARAM_INT]);
	}

}
