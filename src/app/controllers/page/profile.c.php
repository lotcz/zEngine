<?php
	$this->setPageTitle('User Profile');

	if (!($this->z->auth->isAuth() && !$this->z->auth->isAnonymous())) {
		$this->redirect('login');
	} else {
		$form = new zForm('user');
		$form->type = 'vertical';
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
			],
			[
				'name' => 'user_phone',
				'label' => 'Phone',
				'type' => 'text'
			]
	 	]);
		$user = $this->z->auth->user;
		if (z::isPost()) {
			$user->set('user_name', z::get('user_name'));
			$user->set('user_phone', z::get('user_phone'));
			$user->save();

			$this->z->messages->success('Váš profil byl aktualizován.');
		}
		$form->prepare($this->z->db, $user);
		$this->setData('form', $form);
	}
