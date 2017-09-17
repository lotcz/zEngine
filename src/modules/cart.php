<?php

require_once __DIR__ . '/../app/models/category.m.php';
require_once __DIR__ . '/../app/models/product.m.php';
require_once __DIR__ . '/../app/models/product_variant.m.php';
require_once __DIR__ . '/../app/models/cart.m.php';

class cartModule extends zModule {

	private $db = null;

	public function onEnabled() {
		$this->requireModule('mysql');
		$this->requireModule('custauth');
		$this->requireModule('alias');
		$this->db = $this->z->core->db;
	}

	public function loadCartTotals($customer_id = null) {
		if (!isset($customer_id)) {
			$customer_id = $this->z->custauth->customer->ival('customer_id');
		}
		$totals = [];
		$sql = 'SELECT SUM(item_price) AS total_cart_price FROM viewProductsInCart WHERE cart_customer_id = ?';
		$statement = zSqlQuery::executeSQL($this->db, $sql, [$customer_id]);
		$result = $statement->get_result();
		if ($row = $result->fetch_assoc()) {
			$totals['total_cart_price'] = parseFloat($row['total_cart_price']);
			$totals['total_cart_price_converted'] = $this->z->i18n->convertMoney($totals['total_cart_price']);
			$totals['total_cart_price_formatted'] = $this->z->i18n->formatMoney($totals['total_cart_price_converted']);
		}
		$statement->close();
		return $totals;
	}

	public function loadCartProducts($customer_id = null) {
		if (!isset($customer_id)) {
			$customer_id = $this->z->custauth->customer->ival('customer_id');
		}
		return ProductModel::loadCart($this->db, $customer_id);
	}

	public function emptyCart($customer_id = null) {
		if (!isset($customer_id)) {
			$customer_id = $this->z->custauth->customer->ival('customer_id');
		}
		return zSqlQuery::del($this->db, 'cart', 'cart_customer_id = ?', [$customer_id]);
	}

	public function transferCart($old_customer_id, $new_customer_id) {
		$data = [];
		$data['cart_customer_id'] = $new_customer_id;
		return zSqlQuery::update($this->db, 'cart', $data, 'cart_customer_id = ?', [$old_customer_id]);
	}

}
