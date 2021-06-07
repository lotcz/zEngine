<?php

	require_once __DIR__ . '/../../../models/user.m.php';
	require_once __DIR__ . '/../../../models/admin.m.php';

	$this->renderAdminForm(
		'AdminModel',
		[
			[
				'name' => 'user_state',
				'label' => 'Status',
				'type' => 'static_custom',
				'custom_function' => 'UserModel::getUserStatusLabel'
			],
			[
				'name' => 'user_name',
				'label' => 'Full Name',
				'type' => 'text'
			],
			[
				'name' => 'user_login',
				'label' => 'Login',
				'type' => 'text'
			],
			[
				'name' => 'user_email',
				'label' => 'E-mail',
				'type' => 'text',
				'required' => true,
				'validations' => [['type' => 'email']]
			],
			[
				'name' => 'user_admin_role_id',
				'label' => 'Role',
				'type' => 'select',
				'select_table' => 'admin_role',
				'select_data' => AdminRoleModel::all($this->z->db),
				'select_id_field' => 'admin_role_id',
				'select_label_field' => 'admin_role_name'
			],
			[
				'name' => 'user_language_id',
				'label' => 'Language',
				'type' => 'select',
				'select_table' => 'languages',
				'select_data' => LanguageModel::all($this->z->db),
				'select_id_field' => 'language_id',
				'select_label_field' => 'language_name'
			],
			[
				'name' => 'user_failed_attempts',
				'label' => 'Failed Attempts',
				'type' => 'static'
			],
			[
				'name' => 'user_last_access',
				'label' => 'Last Visit',
				'type' => 'staticdate'
			],
			[
				'name' => 'user_buttons',
				'label' => 'Commands',
				'type' => 'buttons',
				'buttons' => [
					['type' => 'link', 'label' => 'Change Password', 'link_url' => 'admin/change-password?user_id=' . $this->getPath(-1), 'css' => 'btn btn-primary m-2' ]
				]
			]
		]
	);
