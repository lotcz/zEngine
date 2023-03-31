<?php
	$this->setPageTitle('Sign In');
	$this->requireModule('forms');

	if ($this->z->auth->isAuth()) {
		$this->redirectBack($this->z->auth->public_login_home);
	}

	if (z::isPost()) {
		$email = $this->xssafe(z::get('email'));
		$password = z::get('password');

		if (!zForm::validate_email($email)) {
			$this->z->messages->error($this->t('E-mail address is not in correct form! Please enter valid e-mail address.'));
		} elseif (!zForm::validate_length($password, 1)) {
			$this->z->messages->error($this->t('Please enter your password.'));
		} else {
			if ($this->z->auth->login($email, $password)) {
				$this->redirectBack($this->z->auth->public_login_home);
			} else {
				$this->z->messages->error($this->t('Login unsuccessful!'));
			}
		}
	}
