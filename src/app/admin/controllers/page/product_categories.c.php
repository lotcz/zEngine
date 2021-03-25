<?php

	$this->setPageTitle('Categories');
	$this->renderAdminTable(
		'product_category',
		[
			[
				'name' => 'product_category_name',
				'label' => 'Name'
			],
			[
				'name' => 'product_category_description',
				'label' => 'Description'
			]
		]
	);
