<?php

require_once __DIR__ . '/../app/models/order.m.php';
require_once __DIR__ . '/../app/models/order_product.m.php';
require_once __DIR__ . '/../app/models/order_state.m.php';
require_once __DIR__ . '/../app/models/payment_type.m.php';
require_once __DIR__ . '/../app/models/delivery_type.m.php';

/**
* Module that handles e-shops.
*/
class shopModule extends zModule {

	public function onEnabled() {
		$this->requireModule('cart');
	}

	public function createOrder() {
		$db = $this->z->core->db;
		$customer = $this->z->core->getCustomer();
		$delivery_type = new DeliveryTypeModel($db, $customer->ival('customer_delivery_type_id'));
		$payment_type = new PaymentTypeModel($db, $customer->ival('customer_payment_type_id'));
		$currency = $this->z->i18n->selected_currency;

		$order = new OrderModel($db);
		$order->data['order_number'] = OrderModel::getNewOrderNumber($db);
		$order->data['order_order_state_id'] = OrderStateModel::$new;
		$order->data['order_customer_id'] = $customer->ival('customer_id');
		$order->data['order_delivery_type_id'] = $delivery_type->ival('delivery_type_id');
		$order->data['order_payment_type_id'] = $payment_type->ival('payment_type_id');

		if ($customer->bval('customer_use_ship_address')) {
			$order->data['order_ship_name'] = $customer->val('customer_ship_name');
			$order->data['order_ship_city'] = $customer->val('customer_ship_city');
			$order->data['order_ship_street'] = $customer->val('customer_ship_street');
			$order->data['order_ship_zip'] = $customer->val('customer_ship_zip');
		} else {
			$order->data['order_ship_name'] = $customer->val('customer_name');
			$order->data['order_ship_city'] = $customer->val('customer_address_city');
			$order->data['order_ship_street'] = $customer->val('customer_address_street');
			$order->data['order_ship_zip'] = $customer->val('customer_address_zip');
		}

		$order->data['order_currency_id'] = $currency->ival('currency_id');
		$delivery_price = $currency->convert($delivery_type->fval('delivery_type_price'));
		$order->data['order_delivery_type_price'] = $delivery_price;
		$payment_price = $currency->convert($payment_type->fval('payment_type_price'));
		$order->data['order_payment_type_price'] = $payment_price;

		$products = $this->z->cart->loadCartProducts();
		$total_cart_value = 0;
		$order_products = [];
		foreach ($products as $product) {
			$order_product = new OrderProductModel($db);
			$order_product->set('order_product_product_id', $product->ival('product_id'));
			$order_product->set('order_product_variant_id', $product->ival('car_variant_id'));
			$order_product->set('order_product_name', $product->val('product_name'));
			$order_product->set('order_product_variant_name', $product->val('product_variant_name'));
			$order_product->set('order_product_price', z::parseFloat($currency->convert($product->fval('actual_price'))));
			$order_product->set('order_product_count', $product->ival('cart_count'));
			$item_price = $currency->convert($product->fval('item_price'));
			$order_product->set('order_product_item_price', z::parseFloat($item_price));
			$total_cart_value += $item_price;
			$order_products[] = $order_product;
		}

		$order->data['order_total_cart_price'] = $total_cart_value;
		$order->data['order_total_price'] = $total_cart_value + $delivery_price + $payment_price;

		if ($order->save()) {
			foreach ($order_products as $order_product) {
				$order_product->set('order_product_order_id', $order->ival('order_id'));
				$order_product->save();
			}
			$this->z->cart->emptyCart();
			$this->sendOrderConfirmationEmail($order, $delivery_type, $payment_type, $currency);
		}
		return $order;
	}

	/* EMAILS */

	public function sendOrderConfirmationEmail($order, $delivery_type, $payment_type, $currency) {
		$customer_email = $this->z->core->getCustomer()->val('customer_email');
		$subject = $this->z->custauth->getEmailSubject($this->z->core->t('Your order n. %s has been accepted.', $order->val('order_number')));
		$data = [
			'order' => $order,
			'delivery_type' => $delivery_type,
			'payment_type' => $payment_type,
			'currency' => $currency
		];
		$this->z->emails->renderAndSend($customer_email, $subject, 'confirm', $data);
	}

}
