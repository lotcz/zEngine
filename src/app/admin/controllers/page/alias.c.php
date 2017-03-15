<?php

	require_once __DIR__ . '/../../../models/alias.m.php';
	
	$this->renderAdminForm(
		'alias',
		'AliasModel',	
		[		
			[
				'name' => 'alias_url',
				'label' => 'URL',
				'type' => 'text',
				'validations' => [['type' => 'length', 'param' => 1]]
			],
			[
				'name' => 'alias_path',
				'label' => 'Path',
				'type' => 'text',
				'validations' => [['type' => 'length', 'param' => 1]]
			]		
			
		]
	);