<?php
	$this->setPageTitle('Forgotten Password');

	if (z::isPost()) {
		$user = new UserModel($this->z->db);
		$user->loadByLoginOrEmail(z::get('email'));
		if ($user->is_loaded) {
			if ($user->isDeactivated()) {
				$this->message('User is deactivated, cannot change password!', 'warning');
			} else if ((strlen($user->val('user_reset_password_hash')) > 0) && ($user->dtval('user_reset_password_expires') > time())) {
				$this->message('An e-mail was already sent to your address with reset password instructions.', 'warning');
			} else {
				$this->z->auth->resetPassword($user);
				$this->message('An e-mail was sent to your address with reset password instructions.');
			}
		} else {
			// increase ip address failed attempts
			$this->z->security->saveFailedAttempt($this->z->db);
			$this->message('This e-mail address or login was not found in our database.','error');
		}
	}
