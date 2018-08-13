<?php

	$this->setPageTitle('Currencies');
	$this->renderAdminTable(
		'currency', 		
		'currency',
		[
			[
				'name' => 'currency_name',
				'label' => 'Name'
			],
			[
				'name' => 'currency_format',
				'label' => 'Format'
			],
			[
				'name' => 'currency_value',
				'label' => 'Value'
			]
		]
	);
