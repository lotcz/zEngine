<?php

require_once __DIR__ . '/../models/product.m.php';
require_once __DIR__ . '/../models/product_category.m.php';
require_once __DIR__ . '/../models/cart_item.m.php';
require_once __DIR__ . '/../models/delivery_type.m.php';
require_once __DIR__ . '/../models/payment_type.m.php';
require_once __DIR__ . '/../models/order_state.m.php';
require_once __DIR__ . '/../models/order_product.m.php';
require_once __DIR__ . '/../models/order.m.php';
require_once __DIR__ . '/../models/order_product.m.php';
require_once __DIR__ . '/../models/customer.m.php';

/**
* e-shop
*/
class shopModule extends zModule {

	public array $depends_on = ['db', 'images', 'auth'];

	public $customer = null;

	public function getCustomer() {
		if ($this->customer === null) {
			if (!$this->z->auth->isAuth()) {
				$this->z->messages->error('Nemůžeme tě přihlásit. Máš asi vypnuté cookies.');
				return;
			}
			$user_id = $this->z->auth->user->ival('user_id');
			$this->customer = new CustomerModel($this->z->db);
			$this->customer->loadByUserId($user_id);

			if (!$this->customer->is_loaded) {
				$this->customer->set('customer_user_id', $user_id);
				$this->customer->save();
			}
		}
		return $this->customer;
	}

	public function addToCart(ProductModel $product, int $quantity = 1) {
		$customer = $this->getCustomer();
		$product_id = $product->ival('product_id');
		$cart_item = $this->loadCartItemByProductId($product_id);
		if (!$cart_item->is_loaded) {
			$cart_item->set('cart_item_product_id', $product_id);
			$cart_item->set('cart_item_customer_id', $customer->ival('customer_id'));
		}
		$cart_item->set('cart_item_quantity', $quantity);
		$cart_item->save();
	}

	public function loadCart() {
		$customer = $this->getCustomer();
		$products = ProductModel::select($this->z->db, 'viewProductsInCart', 'cart_item_customer_id = ?', 'product_name', null, [$customer->ival('customer_id')], [PDO::PARAM_INT]);
		return $products;
	}

	public function loadCartItemByProductId(int $product_id) {
		$customer = $this->getCustomer();
		$cart_item = new CartItemModel($this->z->db);
		$cart_item->loadByProductAndCustomer($product_id, $customer->ival('customer_id'));
		return $cart_item;
	}

	public function getLastOrderId() {
		$sql = 'SELECT MAX(order_id) as id FROM `order`';
		$statement = $this->z->db->executeQuery($sql);
		$id = 0;
		if ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
			$id = $row['id'];
		}
		$statement->closeCursor();
		return $id;
	}

	public function createNewOrderNumber() {
		$last_order_id = $this->getLastOrderId();
		$year = date("Y");
		return $year . str_pad($last_order_id + 1, 4, "0");
	}

	public function emptyCart() {
		$customer = $this->getCustomer();
		$this->z->db->executeDeleteQuery('cart_item', 'cart_item_customer_id = ?', [$customer->ival('customer_id')], [PDO::PARAM_INT]);
	}

}
