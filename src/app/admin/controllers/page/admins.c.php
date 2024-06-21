<?php

	$this->z->admin->checkAnyRole([AdminRoleModel::role_superuser, AdminRoleModel::role_admin]);

	$this->setPageTitle('Administrators');
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
				'name' => 'admin_role_name',
				'label' => 'Role'
			],
			[
				'name' => 'user_last_access',
				'label' => 'Last Visit',
				'type' => 'datetime'
			]
		],
		'view_administrators',
		['user_id', 'user_name', 'user_login', 'user_email', 'user_last_access', 'admin_role_name'],
		'user_id'
	);
