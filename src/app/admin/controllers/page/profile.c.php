<?php

	require_once __DIR__ . '/../../../models/user.m.php';

	$user_id = z::parseInt($this->getPath(-1));
	if ($user_id > 0) {
		if ($user_id != $this->z->auth->user->ival('user_id')) {
			$this->redirect('admin', 403);
		}
	}

	$this->setPageTitle('User Profile');

	$form = new zForm('user');
	$form->type = 'vertical';
	$form->render_wrapper = true;
	$form->suppress_return = true;

	$form->add(
		[
			[
				'name' => 'user_id',
				'type' => 'hidden'
			],
			[
				'name' => 'user_email',
				'label' => 'E-mail',
				'type' => 'static'
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
				'name' => 'user_language_id',
				'label' => 'Language',
				'type' => 'select',
				'select_table' => 'languages',
				'select_data' => LanguageModel::all($this->z->db),
				'select_id_field' => 'language_id',
				'select_label_field' => 'language_name'
			],
			[
				'name' => 'form_buttons',
				'type' => 'buttons',
				'buttons' => [
					[
						'type' => 'submit',
						'label' => 'Save',
						'onclick' => 'validateForm_' . $form->id . '(event, true);',
						'css' => 'btn btn-success m-2'
					]
				]
			]
		]
	);

	$this->z->forms->processForm($form, 'UserModel');

	$this->setData('form', $form);
	$this->setPageView('admin');
