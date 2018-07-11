<?php

	$this->setPageTitle('Forgotten Password');
	$this->z->core->includeJS('resources/forms.js');

	if (z::isPost()) {
		$user = new UserModel($this->db);
		$user->loadByLoginOrEmail(z::get('email'));
		if ($user->is_loaded) {
			$reset_token = $this->z->auth->generateResetPasswordToken();
			$expires = time() + $this->z->auth->getConfigValue('reset_password_expires');
			$user->set('user_reset_password_hash', $this->z->auth->hashPassword($reset_token));
			$user->set('user_reset_password_expires', zSqlQuery::mysqlTimestamp($expires));
			$user->save();

			$email_text = $this->t('To reset your password, visit this link: %s?user=%s&reset_token=%s. This link is only valid for %d days.', $this->url('admin-reset'), $user->val('user_email'), $reset_token, 7);
			$this->z->emails->sendPlain($user->val('user_email'), $this->t('Forgotten Password'), $email_text);
			$this->message('An e-mail was sent to your address with reset password instructions.');
		} else {
			// increase ip address failed attempts
			IpFailedAttemptModel::saveFailedAttempt($this->db);
			$this->message('This e-mail address or login was not found in our database.','error');
		}
	}
