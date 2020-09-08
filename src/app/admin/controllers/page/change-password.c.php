<?php
	$this->setPageTitle('Change Password');

	if (z::isPost()) {

		$password = z::get('password');
		$password_confirm = z::get('password_confirm');

		if ($this->z->auth->isValidPassword($password)) {

			if ($password == $password_confirm) {
				$user = $this->z->auth->user;
				$user->set('user_password_hash', $this->z->auth->hashPassword($password));
				$user->save();
				$this->message('Your password was successfully changed.', 'success');

			} else {
				$this->z->messages->error($this->t('Passwords don\'t match.'));
			}
		} else {
			$this->z->messages->error($this->t('Invalid password.'));
		}

	}
