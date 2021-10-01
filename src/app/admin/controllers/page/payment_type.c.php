<?php

	$this->renderAdminForm(
		'PaymentTypeModel',
		[
			[
				'name' => 'payment_type_name',
				'label' => 'Name',
				'type' => 'text',
				'validations' => [['type' => 'length', 'param' => 1]]
			],
			[
				'name' => 'payment_type_price',
				'label' => 'Price',
				'type' => 'text',
				'validations' => [['type' => 'price']]
			],
			[
				'name' => 'payment_type_min_order_cost',
				'label' => 'Min. order cost',
				'type' => 'text',
				'validations' => [['type' => 'price']]
			],
			[
				'name' => 'payment_type_is_default',
				'label' => 'Is Default',
				'type' => 'bool'
			]
		]
	);
