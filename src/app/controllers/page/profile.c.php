<?php
	$this->requireModule('forms');
	$this->setPageTitle('User Profile');

	if (!($this->auth->isAuth() && !$this->z->auth->isAnonymous())) {
		$this->redirect('login');
	} else {
		$form = new zForm('user');
		$form->type = '';
		$form->add([
			[
			  'name' => 'user_email',
			  'label' => 'E-mail',
			  'type' => 'text',
				'disabled' => 'disabled'
			],
			[
			'name' => 'user_name',
			'label' => 'Full name',
			'type' => 'text'
			]
	 	]);
		$user = $this->z->auth->user;
		if (z::isPost()) {
			$user->set('user_name', z::get('user_name'));
			$user->save();
		}
		$form->prepare($this->z->db, $user);
		$this->setData('form', $form);
	}
