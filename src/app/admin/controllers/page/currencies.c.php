<?php

	$this->setPageTitle('Currencies');	
	$this->renderAdminTable(
		'currencies', 		
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