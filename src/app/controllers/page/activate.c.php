<?php
	$this->setPageTitle('Account activation');

	$activation_token = z::get('activation_token');
	$customer_email = z::get('email');

	if (!(isset($activation_token) && isset($customer_email))) {
		$this->message('This page should only be accessed from link sent to your e-mail.', 'error');
	} else {
		$user = new UserModel($this->z->db);
		$user->loadByLoginOrEmail($customer_email);
		$token_valid = $this->z->auth->verifyPassword($activation_token, $user->val('user_reset_password_hash'));

		if (!($user->is_loaded && $token_valid)) {
			$this->message('Your link seems to be invalid.', 'error');
		} else {
			if ($user->resetTokenExpired()) {
				$message = $this->t('Your activation link has expired. Ask for <a href="%s">new password</a>.', $this->url('reset-password'));
				$this->z->messages->error($message);
			} else {
				$user->set('user_state', UserModel::user_state_active);
				$user->set('user_reset_password_hash', null);
				$user->set('user_reset_password_expires', null);
				$user->save();
				$this->z->auth->createSession($user);
				$this->message('Your account was successfully activated.', 'success');

			}
		}
	}
