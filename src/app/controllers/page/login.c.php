<?php
	$this->setPageTitle('Sign In');

	$redirect = fn () => $this->redirectBack($this->z->auth->hasAnyRole() ? 'admin' : $this->z->auth->public_login_home);

	if ($this->z->auth->isAuth()) {
		$redirect();
	}

	if (z::isPost()) {
		$email = z::xssafe(z::get('email'));
		$password = z::get('password');

		$honeypot = z::get('name');
		if (!empty($honeypot)) {
			$this->z->security->saveFailedAttempt();
			sleep(5);
			die(); // it is a robot
		}

		$token = z::get('form_token');
		if (!$this->z->forms->verifyProtectionTokenHash('form_login', $token)) {
			$this->z->security->saveFailedAttempt();
			$password = null; // this will prevent login
			sleep(5);
			$this->message("Platnost formuláře vypršela", 'danger');
		}

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

	$this->setData('form_token', $this->z->forms->createProtectionTokenHash('form_login'));
