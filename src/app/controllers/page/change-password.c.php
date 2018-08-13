<?php
	$this->requireModule('forms');
	$this->setPageTitle('Change Password');

	if (!($this->z->auth->isAuth() && !$this->z->auth->isAnonymous())) {
		$this->redirect('login');
	}

	$form = new zForm('changepass_form');
	$form->add([
		[
			'name' => 'password',
			'label' => 'Password',
			'type' => 'password',
			'validations' => [['type' => 'password']]
		],
		[
			'name' => 'password_confirm',
			'label' => 'Confirm Password',
			'type' => 'password',
			'validations' => [['type' => 'confirm', 'param' => 'password']]
		]
	]);

	if (z::isPost()) {

		$password = z::get('password');
		$password_confirm = z::get('password_confirm');

		if ($this->z->auth->isValidPassword($password)) {

			if ($password == $password_confirm) {

				$user = $this->z->auth->user;
				$user->data['user_password_hash'] = $this->z->auth->hashPassword($password);
				$user->save();
				$this->message('Your password was successfully changed.', 'success');

			} else {
				$this->z->messages->error($this->t('Passwords don\'t match.'));
			}
		} else {
			$this->z->messages->error($this->t('Invalid password.'));
		}

	}
