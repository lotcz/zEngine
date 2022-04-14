<?php

	$this->z->admin->checkAnyRole();

	$this->setPageTitle('Currencies');
	$this->renderAdminTable(
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
