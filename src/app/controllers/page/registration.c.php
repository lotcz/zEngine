<?php
	$this->setPageTitle('Registration');
	$this->requireModule('forms');

	if ($this->isCustAuth() && !$this->z->custauth->isAnonymous()) {
		$this->redirect('profile');
	} elseif (z::isPost()) {

		$full_name = z::xssafe(z::get('full_name'));
		$email = trim(strtolower(z::get('email')));
		$password = z::get('password');
		$password_confirm = z::get('password_confirm');

		// validate email and password
		if ($this->z->custauth->isValidEmail($email) && $this->z->custauth->isValidPassword($password)) {

			if ($password == $password_confirm) {

				// check if email exists
				$existing_customer = new CustomerModel($this->z->db);
				$existing_customer->loadByEmail($email);
				if ($existing_customer->is_loaded) {
					$this->z->messages->error($this->t('This email is already used!'));
				} else {
					$this->z->custauth->registerCustomer($email, $password, $full_name);
				}
			} else {
				$this->z->messages->error($this->t('Passwords don\'t match.'));
			}
		} else {
			$this->z->messages->error($this->t('Invalid password or email.'));
		}

	}

	$this->includeJS('resources/registration.js', false, 'bottom');
	$this->insertJS(
		[
			'z_email_check_ajax_url' => $this->url('json/default/emailexists')
		]
	);
