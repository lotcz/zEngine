<?php

	require_once __DIR__ . '/../../../models/order.m.php';

	$this->renderAdminForm(
		'OrderModel',
		[
			[
				'name' => 'order_id',
				'label' => 'Order ID',
				'type' => 'hidden'
			],
			[
				'name' => 'order_created',
				'label' => 'Date',
				'type' => 'staticdate',
				'disabled' => 'disabled'
			],
			[
				'name' => 'order_order_state_id',
				'label' => 'Status',
				'type' => 'select',
				'select_table' => 'order_state',
				'select_id_field' => 'order_state_id',
				'select_label_field' => 'order_state_name',
				'select_label_localized' => true
			],
			[
				'name' => 'order_customer_id',
				'label' => 'Customer',
				'type' => 'foreign_key_link',
				'link_table' => 'customer',
				'link_template' => 'admin/customer/edit/%d',
				'link_id_field' => 'customer_id',
				'link_label_field' => 'customer_email'
			],
			[
				'name' => 'order_ship_name',
				'label' => 'Full name',
				'type' => 'text',
				'validations' => [['type' => 'length', 'param' => 1]]
			],
			[
				'name' => 'order_ship_city',
				'label' => 'City',
				'type' => 'text'
			],
			[
				'name' => 'order_ship_street',
				'label' => 'Street',
				'type' => 'text'
			],
			[
				'name' => 'order_ship_zip',
				'label' => 'ZIP',
				'type' => 'text'
			],

		]
	);
