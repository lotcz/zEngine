<?php

class OrderModel extends zModel {
	
	public $table_name = 'orders';
	public $id_name = 'order_id';	

	public function loadProducts() {
		$this->products = OrderProduct::select(
		/* db */		$this->db, 
		/* table */		'viewOrderProducts', 
		/* where */		'order_product_order_id = ?',
		/* bindings */	[ $this->ival('order_id') ],
		/* types */		'i',
		/* paging */	null,
		/* orderby */	'order_product_name'
		);		
	}
	
	static function createOrder($db, $customer) {		
		$delivery_type = new DeliveryType($db, $customer->ival('customer_delivery_type_id'));	
		$payment_type = new PaymentType($db, $customer->ival('customer_payment_type_id'));
		$currency = new Currency($db, $customer->ival('customer_currency_id'));
		
		$order = new Order($db);
		$order->data['order_order_state_id'] = OrderState::$new;
		$order->data['order_customer_id'] = $customer->ival('customer_id');
		$order->data['order_delivery_type_id'] = $delivery_type->ival('delivery_type_id');
		$order->data['order_payment_type_id'] = $payment_type->ival('payment_type_id');			
		$order->data['order_ship_name'] = $customer->val('customer_ship_name');
		$order->data['order_ship_city'] = $customer->val('customer_ship_city');
		$order->data['order_ship_street'] = $customer->val('customer_ship_street');
		$order->data['order_ship_zip'] = $customer->val('customer_ship_zip');		
		
		$order->data['order_currency_id'] = $currency->ival('currency_id');
		$delivery_price = $currency->convert($delivery_type->fval('delivery_type_price'));
		$order->data['order_delivery_type_price'] = $delivery_price;
		$payment_price = $currency->convert($payment_type->fval('payment_type_price'));
		$order->data['order_payment_type_price'] = $payment_price;
	
		$products = Product::loadCart($db, $customer->ival('customer_id'));
		$total_cart_value = 0;
		
		$order_products = [];
		foreach ($products as $product) {
			$order_product = new OrderProduct($db);			
			$order_product->set('order_product_product_id', $product->ival('product_id'));
			$order_product->set('order_product_variant_id', $product->ival('car_variant_id'));
			$order_product->set('order_product_name', $product->val('product_name'));
			$order_product->set('order_product_variant_name', $product->val('product_variant_name'));
			$order_product->set('order_product_price',  parseFloat($currency->convert($product->fval('actual_price'))));
			$order_product->set('order_product_count', $product->ival('cart_count'));
			$item_price = $currency->convert($product->fval('item_price'));
			$order_product->set('order_product_item_price',  parseFloat($item_price));
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
			Cart::emptyCart($db, $customer->ival('customer_id'));
		}
		return $order;
	}
	
}