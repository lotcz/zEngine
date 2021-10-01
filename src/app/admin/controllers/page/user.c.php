<?php

	require_once __DIR__ . '/../../../models/user.m.php';
	require_once __DIR__ . '/../../../models/admin.m.php';

	// create admin account if doesnt exist yet
	// TODO: replace with proper administration of administrators
	$onAfterUpdate = function($z, $form, $data) {
		$user_id = $data->ival('user_id');
		if ($user_id <= 0) return;

		$admin = new AdminModel($this->z->db);
		$admin->loadByUserId($user_id);
		if (!$admin->is_loaded) {
			$admin->set('admin_user_id', $user_id);
			$admin->save();
		}
	};

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
			],
			[
				'name' => 'user_buttons',
				'label' => 'Commands',
				'type' => 'buttons',
				'buttons' => [
					['type' => 'link', 'label' => 'Change Password', 'link_url' => 'admin/change-password?user_id=' . $this->getPath(-1), 'css' => 'btn btn-primary m-2' ]
				]
			]
		],
		null,
		$onAfterUpdate
	);
