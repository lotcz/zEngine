<?php

	$this->setPageTitle('Registration');

	if ($this->z->auth->isAuth() && !$this->z->auth->isAnonymous()) {
		$this->redirect('profile');
	} elseif (z::isPost()) {

		$full_name = z::xssafe(z::get('full_name'));
		$email = z::trim(z::get('email'));
		$phone = z::trim(z::get('phone'));
		$password = z::get('password');
		$password_confirm = z::get('password_confirm');

		$honeypot = z::get('name');
		if (!empty($honeypot)) {
			$this->z->security->saveFailedAttempt();
			sleep(5);
			die(); // it is a robot
		}

		$token = z::get('form_token');
		if (!$this->z->forms->verifyProtectionTokenHash('register_form', $token)) {
			$this->z->security->saveFailedAttempt();
			$password = null; // this will prevent registration
			sleep(5);
			$this->message("Platnost formuláře vypršela", 'danger');
		}

		// validate email and password
		if ($this->z->forms->fieldValidation('email', $email) && $this->z->auth->isValidPassword($password)) {
			$email = strtolower($email);
			if ($password == $password_confirm) {
				// check if email exists
				$existing_user = new UserModel($this->z->db);
				$existing_user->loadByLoginOrEmail($email);
				if ($existing_user->is_loaded) {
					$this->z->messages->error($this->t('This email is already used!'));
				} else {
					$this->z->auth->registerUser($full_name, null, $email, $phone, $password);
				}
			} else {
				$this->z->messages->error($this->t('Passwords don\'t match.'));
			}
		} else {
			$this->z->messages->error($this->t('Invalid password or email.'));
		}

	}

	$this->includeJS('resources/registration.js', 'bottom');
	$this->insertJS(
		[
			'z_email_check_ajax_url' => $this->url('json/default/emailexists')
		]
	);

	$this->setData('form_token', $this->z->forms->createProtectionTokenHash('register_form'));
