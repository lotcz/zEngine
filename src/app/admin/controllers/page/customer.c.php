<?php
	require_once __DIR__ . '/../../../models/customer.m.php';
	
	$this->renderAdminForm(
		'customer',
		'CustomerModel',
		[			
			[
				'name' => 'customer_state',
				'label' => 'Status',
				'type' => 'static_custom',
				'custom_function' => 'CustomerModel::getCustomerStatusLabel'
			],
			[
				'name' => 'customer_email',
				'label' => 'E-mail',
				'type' => 'text',
				'validations' => [['type' => 'email']]
			],
			[
				'name' => 'customer_language_id',
				'label' => 'Language',
				'type' => 'select',
				'select_table' => 'languages',
				'select_id_field' => 'language_id',
				'select_label_field' => 'language_name'
			],
			[
				'name' => 'customer_currency_id',
				'label' => 'Currency',
				'type' => 'select',
				'select_table' => 'currencies',
				'select_id_field' => 'currency_id',
				'select_label_field' => 'currency_name'
			],			
			[
				'name' => 'customer_name',
				'label' => 'Name',
				'type' => 'text',
				'validations' => [['type' => 'maxlen', 'param' => 50]]
			],
			[
				'name' => 'customer_created',
				'label' => 'Date',
				'type' => 'staticdate'
			],
			[
				'name' => 'customer_last_access',
				'label' => 'Last visited',
				'type' => 'staticdate'
			],
			[
				'name' => 'customer_failed_attempts',
				'label' => 'Failed attempts',
				'type' => 'static'
			]
		]
	);
	
	