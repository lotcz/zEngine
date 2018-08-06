<?php

	require_once __DIR__ . '/../../../models/user.m.php';	
	
	$this->renderAdminForm(
		'user',
		'UserModel',
		[	
			[
				'name' => 'user_deleted',
				'label' => 'User Deactivated',
				'type' => 'bool'
			],
			[
				'name' => 'user_is_superuser',
				'label' => 'Is Superuser',
				'type' => 'bool'
			],
			[
				'name' => 'user_login',
				'label' => 'Login',
				'type' => 'text',
				'required' => true,
				'validations' => [['type' => 'length', 'param' => 1]]
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
				'select_data' => LanguageModel::all($this->db),
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