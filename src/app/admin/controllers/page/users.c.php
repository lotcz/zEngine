<?php

	$this->setPageTitle('Users');
	$this->renderAdminTable(
		'user',
		[
			[
				'name' => 'user_name',
				'label' => 'Full Name'
			],
			[
				'name' => 'user_login',
				'label' => 'Login'
			],
			[
				'name' => 'user_email',
				'label' => 'E-mail'
			],
			[
				'name' => 'user_last_access',
				'label' => 'Last Visit',
				'type' => 'datetime'
			]
		]
	);
