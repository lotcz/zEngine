<?php

	require_once __DIR__ . '/../../../models/role.m.php';	
	
	$this->renderAdminForm(
		'role',
		'RoleModel',
		[		
			[
				'name' => 'role_name',
				'label' => 'Name',
				'type' => 'text',
				'validations' => [['type' => 'length', 'param' => 1]]
			],
			[
				'name' => 'role_description',
				'label' => 'Description',
				'type' => 'text'			
			]		
		]
	);