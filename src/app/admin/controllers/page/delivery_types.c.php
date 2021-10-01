<?php

	$this->setPageTitle('Delivery types');
	$this->renderAdminTable(
		'delivery_type',
		[
			[
				'name' => 'delivery_type_name',
				'label' => 'Name'
			]
		]
	);
