<?php
	$this->requireModule('forms');
	$this->setPageTitle('Forgotten Password');

	if (z::isPost()) {
		$customer = new CustomerModel($this->z->db);
		$customer->loadByEmail(z::get('email'));
		if ($customer->is_loaded) {
			$reset_token = $this->z->custauth->generateResetPasswordToken();
			$expires = time() + $this->z->custauth->getConfigValue('reset_password_expires');
			$customer->set('customer_reset_password_hash', $this->z->custauth->hashPassword($reset_token));
			$customer->set('customer_reset_password_expires', z::mysqlTimestamp($expires));
			$customer->save();

			$link = sprintf('%s?email=%s&reset_token=%s', $this->url('reset-password'), $customer->val('customer_email'), $reset_token);
			$email_text = $this->t("To reset your password, visit this link:\n\n%s\n\nThis link is only valid for %d days.", $link, 7);

			$this->z->emails->sendPlain($customer->val('customer_email'), $this->t('Forgotten Password'), $email_text);
			$this->message('An e-mail was sent to your address with reset password instructions.');
		} else {
			// increase ip address failed attempts
			IpFailedAttemptModel::saveFailedAttempt($this->z->db);
			$this->message('This e-mail address or login was not found in our database.','error');
		}
	}
