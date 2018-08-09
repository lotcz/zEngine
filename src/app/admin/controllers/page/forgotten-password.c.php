<?php
	$this->setPageTitle('Forgotten Password');

	if (z::isPost()) {
		$user = new UserModel($this->z->db);
		$user->loadByLoginOrEmail(z::get('email'));
		if ($user->is_loaded) {
			if ((strlen($user->val('user_reset_password_hash')) > 0) && ($user->dtval('user_reset_password_expires') > z::mysqlDatetime(time()))) {
				$this->message('An e-mail was already sent to your address with reset password instructions.', 'warning');
			} else {
				$reset_token = $this->z->auth->generateResetPasswordToken();
				$expires = time() + $this->z->auth->getConfigValue('reset_password_expires');
				$user->set('user_reset_password_hash', $this->z->auth->hashPassword($reset_token));
				$user->set('user_reset_password_expires', z::mysqlTimestamp($expires));
				$user->save();

				$email_text = $this->t('To reset your password, visit this link: %s?user=%s&reset_token=%s. This link is only valid for %d days.', $this->url('admin/reset-password'), $user->val('user_email'), $reset_token, 7);
				$this->z->emails->sendPlain($user->val('user_email'), $this->t('Forgotten Password'), $email_text);
				$this->message('An e-mail was sent to your address with reset password instructions.');
			}
		} else {
			// increase ip address failed attempts
			IpFailedAttemptModel::saveFailedAttempt($this->z->db);
			$this->message('E-mail address not found!','error');
		}
	}