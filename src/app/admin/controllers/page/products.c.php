<?php

	$this->setPageTitle('Products');
	$this->renderAdminTable(
		'product',
		[
			[
				'name' => 'product_id',
				'label' => 'ID'
			],
			[
				'name' => 'product_name',
				'label' => 'Name'
			],
			[
				'name' => 'product_price',
				'label' => 'Price'
			]
		],
		null,
		['product_id', 'product_name'],
		'product_id',
		['product_name', 'product_description']
	);
