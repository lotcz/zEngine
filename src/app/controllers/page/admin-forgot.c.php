<?php

	$this->setPageTitle('Forgotten Password');
	$this->z->core->includeJS('resources/forms.js');

	if ($this->isPost()) {
		$user = new UserModel($this->db);
		$user->loadByLoginOrEmail($this->get('email'));
		if ($user->is_loaded) {
			$reset_token = $this->z->auth->generateResetPasswordToken();
			$expires = time() + $this->z->auth->getConfigValue('reset_password_expires');
			$user->data['user_reset_password_hash'] = $this->z->auth->hashPassword($reset_token);
			$user->save();

			$email_text = $this->t('To reset your password, visit this link: %s?reset_token=%s. This link is only valid for %d days.', $this->url(sprintf('admin-reset/%d', $user->ival('user_id'))), $reset_token, 7);
			$this->z->emails->sendPlain($user->val('user_email'), $this->t('Forgotten Password'), $email_text);
			$this->message('An e-mail was sent to your address with reset password instructions.');
		} else {
			// increase ip address failed attempts
			IpFailedAttemptModel::saveFailedAttempt($this->db);
			$this->message('This e-mail address or login was not found in our database.','error');
		}
	}