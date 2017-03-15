<?php

	require_once $home_dir . 'classes/tables.php';
	
	$page_title	= t('Currencies');
	$page_template = 'admin/table';
	
	$table = new AdminTable(
		'currencies', 		
		'currency'
	);
	
	$table->add([		
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
	]);
	
	$table->prepare($db);