<?php
	$this->setPageTitle('Customers');	
	$this->renderAdminTable(
		'customers', 		
		'customer',
		[	
			[
				'name' => 'customer_name',
				'label' => 'Name'			
			],
			[
				'name' => 'customer_email',
				'label' => 'E-mail'			
			],
			[
				'name' => 'customer_failed_attempts',
				'label' => 'Failed Logins'
			]
		]
	);