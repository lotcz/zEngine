<?php

require_once __DIR__ . '/../app/models/order.m.php';
require_once __DIR__ . '/../app/models/order_product.m.php';
require_once __DIR__ . '/../app/models/order_state.m.php';
require_once __DIR__ . '/../app/models/payment_type.m.php';
require_once __DIR__ . '/../app/models/delivery_type.m.php';

class shopModule extends zModule {

	private $db = null;
	
	public function onEnabled() {
		$this->requireModule('cart');
		$this->db = $this->z->core->db;
	}
	
}