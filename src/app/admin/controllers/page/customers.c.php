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
		],
		[
			[
				'name' => 'search_text',
				'label' => 'Search',
				'type' => 'text',
				'filter_fields' => ['customer_name', 'customer_email']
			]
		]
	);
