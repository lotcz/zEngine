<?php
	$this->setPageTitle('Sign In');

	$redirect = fn () => $this->redirectBack($this->z->auth->isAdmin() ? 'admin' : $this->z->auth->public_login_home);

	if ($this->z->auth->isAuth()) {
		$redirect();
	}

	if (z::isPost()) {
		$email = z::xssafe(z::get('email'));
		$password = z::get('password');

		if (!zForm::validate_length($password, 1)) {
			$this->z->messages->error($this->t('Please enter your password.'));
		} else {
			if ($this->z->auth->login($email, $password)) {
				$redirect();
			} else {
				$this->z->messages->error($this->t('Login unsuccessful!'));
			}
		}
	}
