<?php
	
	require_once $home_dir . 'models/currency.m.php';
	require_once $home_dir . 'classes/forms.php';

	$form = new AdminForm('currency');
	$page_template = 'admin/form';

	$form->add([		
		[
			'name' => 'currency_name',
			'label' => 'Name',
			'type' => 'text',
			'validations' => [
				['type' => 'length', 'param' => 1],
			]
		],
		[
			'name' => 'currency_format',
			'label' => 'Format',
			'type' => 'text',
			'hint' => 'This specifies how prices will be displayed in this currency. Put token %s where you want amount to be.'
		],
		[
			'name' => 'currency_value',
			'label' => 'Value',
			'type' => 'text',
			'hint' => 'Put value 1 for default currency.',
			'validations' => [
				['type' => 'price'],
				['type' => 'min', 'param' => 0],
			]
		],
		[
			'name' => 'currency_decimals',
			'label' => 'Displayed decimals',
			'type' => 'text',
			'hint' => 'This specifies how many decimal places will be displayed for prices in this currency.',
			'validations' => [['type' => 'integer']]
		]
		
	]);
	
	Currency::process($db, $form);	