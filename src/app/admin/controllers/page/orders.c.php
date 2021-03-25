<?php

	$this->setPageTitle('Orders');
	$this->renderAdminTable(
		'order',
		[
			[
				'name' => 'order_created',
				'label' => 'Date',
				'type' => 'date'
			],
			[
				'name' => 'order_number',
				'label' => 'Number'
			],
			[
				'name' => 'order_state_name',
				'label' => 'Status'
			],
			[
				'name' => 'customer_email',
				'label' => 'Customer'
			],
			[
				'name' => 'order_payment_code',
				'label' => 'Payment Code'
			],
			[
				'name' => 'order_state_closed',
				'label' => 'Closed'
			]
		],
		'viewOrders',
		null,
		null,
		[
			[
				'name' => 'search_text',
				'label' => 'Search',
				'type' => 'text',
				'filter_fields' => ['customer_email', 'order_number', 'order_payment_code']
			]
		]
	);
