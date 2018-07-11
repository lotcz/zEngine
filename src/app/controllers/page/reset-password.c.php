<?php
	$this->setPageTitle('Reset Password');

	$show_form = false;
	$reset_token = z::get('reset_token');
	$customer_email = z::get('email');
	
	if (isset($reset_token) && isset($customer_email)) {
		$customer = new CustomerModel($this->db);
		$customer->loadByEmail($customer_email);

		$token_not_expired = ($customer->val('customer_reset_password_expires') > zSqlQuery::mysqlTimestamp(time()));
		$token_valid = custauthModule::verifyPassword($reset_token, $customer->val('customer_reset_password_hash'));

		if ($customer->is_loaded && $token_not_expired && $token_valid) {
			$password = z::get('password');
			$password2 = z::get('password2');
			if (isset($password) && isset($password2)) {
				if ($password == $password2) {
					$customer->set('customer_password_hash', $this->z->custauth->hashPassword($password));
					$customer->set('customer_reset_password_hash', null);
					$customer->set('customer_reset_password_expires', null);
					$customer->save();
					$this->message('Your password was successfully changed.', 'success');
				} else {
					$this->message('Passwords don\'t match.', 'error');
				}
			} else {
				$show_form = true;
				$this->z->core->includeJS('resources/forms.js');
				$customer_email = $customer->val('customer_email');
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
	$this->setData('email', $customer_email);
