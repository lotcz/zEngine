<?php

	require_once __DIR__ . '/../../../models/delivery_type.m.php';

	$this->z->admin->checkAnyRole();

	$this->renderAdminForm(
		'DeliveryTypeModel',
		[
			[
				'name' => 'delivery_type_name',
				'label' => 'Name',
				'type' => 'text',
				'validations' => [['type' => 'length', 'param' => 1]]
			],
			[
				'name' => 'delivery_type_price',
				'label' => 'Price',
				'type' => 'text',
				'validations' => [['type' => 'price']]
			],
			[
				'name' => 'delivery_type_min_order_cost',
				'label' => 'Min. order cost',
				'type' => 'text',
				'validations' => [['type' => 'price']]
			]
		]
	);
