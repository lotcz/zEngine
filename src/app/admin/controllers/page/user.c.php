<?php

	require_once __DIR__ . '/../../../models/user.m.php';

	$this->renderAdminForm(
		'UserModel',
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
		]
	);
