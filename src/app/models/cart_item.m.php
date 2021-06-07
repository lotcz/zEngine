<?php

require_once __DIR__ . '/../classes/model.php';

class CartItemModel extends zModel {

	public $table_name = 'cart_item';

	public function loadByProductAndCustomer(int $product_id, int $customer_id) {
		$this->loadSingle('cart_item_product_id = ? and cart_item_customer_id = ?', [$product_id, $customer_id], [PDO::PARAM_INT, PDO::PARAM_INT]);
	}

}
