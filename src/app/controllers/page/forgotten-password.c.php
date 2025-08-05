<?php
	$this->requireModule('forms');
	$this->setPageTitle('Forgotten Password');

	if (z::isPost()) {
		$customer = new UserModel($this->z->db);
		$customer->loadByLoginOrEmail(z::get('email'));
		if ($customer->is_loaded) {
			$reset_token = $this->z->auth->generateResetPasswordToken();
			$expires = time() + $this->z->auth->getConfigValue('reset_password_expires');
			$customer->set('user_reset_password_hash', $this->z->auth->hashPassword($reset_token));
			$customer->set('user_reset_password_expires', z::mysqlTimestamp($expires));
			$customer->save();

			$link = sprintf('%s?email=%s&reset_token=%s', $this->url('reset-password'), $customer->val('user_email'), $reset_token);
			$email_text = $this->t("To reset your password, visit this link:\n\n%s\n\nThis link is only valid for %d days.", $link, 7);

			$this->z->emails->sendPlain($customer->val('user_email'), $this->t('Forgotten Password'), $email_text);
			$this->message('An e-mail was sent to your address with reset password instructions.');
		} else {
			// increase ip address failed attempts
			$this->z->security->saveFailedAttempt($this->z->db);
			$this->message('This e-mail address or login was not found in our database.','error');
		}
	}
