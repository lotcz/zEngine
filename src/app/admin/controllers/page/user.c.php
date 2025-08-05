<?php

	require_once __DIR__ . '/../../../models/user.m.php';

	$this->z->admin->checkIsAdmin();
	$id = z::parseInt($this->getPath(-1));

	$fields = [
		[
			'name' => 'user_state',
			'label' => 'Status',
			'type' => 'select',
			'value' => UserModel::user_state_anonymous,
			'select_label_localized' => true,
			'select_id_field' => 'value',
			'select_label_field' => 'label',
			'select_data' => UserModel::getStateOptions()
		],
		[
			'name' => 'user_email',
			'label' => 'E-mail',
			'type' => 'text',
			'required' => true,
			'validations' => [['type' => 'email']]
		],
		[
			'name' => 'user_login',
			'label' => 'Login',
			'type' => 'text'
		],
		[
			'name' => 'user_name',
			'label' => 'Full Name',
			'type' => 'text'
		],
		[
			'name' => 'user_user_role_id',
			'label' => 'Role',
			'type' => 'select',
			'select_label_localized' => true,
			'select_table' => 'user_role',
			'select_id_field' => 'user_role_id',
			'select_label_field' => 'user_role_name'
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
		]
	];

	if ($id > 0) {
		$fields[] = [
			'name' => 'user_buttons',
			'label' => 'Commands',
			'type' => 'buttons',
			'buttons' => [
				['type' => 'link', 'label' => 'Change Password', 'link_url' => 'admin/change-password?user_id=' . $this->getPath(-1), 'css' => 'btn btn-primary m-2' ]
			]
		];
	}

	$this->renderAdminForm('UserModel', $fields);
