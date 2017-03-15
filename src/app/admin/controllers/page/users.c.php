<?php

	$this->setPageTitle('Administrators');
	$this->renderAdminTable(
		'users', 		
		'user',
		[		
			[
				'name' => 'user_login',
				'label' => 'Login'			
			],
			[
				'name' => 'user_email',
				'label' => 'E-mail'			
			],
			[
				'name' => 'user_last_login',
				'label' => 'Last Login'
			]
		]
	);