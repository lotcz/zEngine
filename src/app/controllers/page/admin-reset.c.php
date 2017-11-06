<?php
	$this->setPageTitle('Reset Password');

	$show_form = false;
	$reset_token = z::get('reset_token');
	$user_email = z::get('user');

	if (isset($reset_token) && isset($user_email)) {
		$user = new UserModel($this->db);
		$user->loadByLoginOrEmail($user_email);

		$token_not_expired = ($user->val('user_reset_password_expires') > zSqlQuery::mysqlTimestamp(time()));
		$token_valid = authModule::verifyPassword($reset_token, $user->val('user_reset_password_hash'));

		if ($user->is_loaded && $token_not_expired && $token_valid) {
			$password = z::get('password');
			$password2 = z::get('password2');
			if (isset($password) && isset($password2)) {
				if ($password == $password2) {
					$user->set('user_password_hash', $this->z->auth->hashPassword($password));
					$user->set('user_reset_password_hash', null);
					$user->set('user_reset_password_expires', null);
					$user->save();
					$this->message('Your password was reset.', 'success');
				} else {
					$this->message('Passwords don\'t match.', 'error');
				}
			} else {
				$show_form = true;
				$this->z->core->includeJS('resources/forms.js');
				$user_email = $user->val('user_email');
				$this->message('Enter your new password.');
			}
		} else {
			$this->message('Your link seems to be invalid.', 'error');
		}
	} else {
		$this->message('This page should only be accessed from link sent to your e-mail.', 'error');
	}

	$this->setData('show_form', $show_form);
	$this->setData('reset_token', $reset_token);
	$this->setData('user_email', $user_email);
