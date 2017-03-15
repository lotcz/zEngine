<?php

	$this->setPageTitle('Roles');	
	$this->renderAdminTable(
		'roles', 		
		'role',
		[		
			[
				'name' => 'role_name',
				'label' => 'Name'			
			],
			[
				'name' => 'role_description',
				'label' => 'Description'
			]
		]
	);