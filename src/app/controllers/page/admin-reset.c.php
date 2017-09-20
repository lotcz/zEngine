<?php
	$this->setPageTitle('Reset Password');

	$show_form = false;
	$reset_token = $this->get('reset_token');
	$user_email = $this->get('user');

	if (isset($reset_token) && isset($user_email)) {
		$user = new UserModel($this->core->db);

		if ($zUser->is_loaded && $zUser->val('user_reset_password_expires') > ModelBase::mysqlTimestamp(time()) && password_verify($reset_token, $zUser->val('user_reset_password_hash'))) {
			if (isset($_POST['password']) && isset($_POST['password2'])) {
				if ($_POST['password'] == $_POST['password2']) {
					$zUser->data['user_password_hash'] = Authentication::hashPassword($_POST['password']);
					$zUser->data['user_reset_password_hash'] = null;
					$zUser->data['user_reset_password_expires'] = null;
					$zUser->save();
					$messages->add(t('Your password was reset.'), 'success');
				} else {
					$messages->error(t('Passwords don\'t match.'));
				}
			} else {
				$data['show_form'] = true;
				$data['user_id'] = $zUser->val('user_id');
				$data['reset_token'] = $reset_token;
				$messages->add(t('Enter your new password.'));
			}
		} else {
			$messages->error(t('Your link seems to be invalid.'));
		}
	} else {
		$messages->error(t('This page should only be accessed from link sent to your e-mail.'));
	}

	$this->setData('show_form', $show_form);
