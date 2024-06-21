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
				'name' => 'user_state',
				'label' => 'Status',
				'type' => 'custom',
				'custom_function' => 'UserModel::getUserStatusLabel'
			],
			[
				'name' => 'user_last_access',
				'label' => 'Last Visit',
				'type' => 'datetime'
			]
		],
		'view_users',
		['user_id', 'user_name', 'user_login', 'user_email', 'user_state', 'user_last_access'],
		'user_id'
	);
