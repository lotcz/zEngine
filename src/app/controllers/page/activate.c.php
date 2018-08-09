<?php
	$this->setPageTitle('Account activation');

	$activation_token = z::get('activation_token');
	$customer_email = z::get('email');

	if (!(isset($activation_token) && isset($customer_email))) {
		$this->message('This page should only be accessed from link sent to your e-mail.', 'error');
	} else {
		$customer = new CustomerModel($this->z->db);
		$customer->loadByEmail($customer_email);
		$token_valid = custauthModule::verifyPassword($activation_token, $customer->val('customer_reset_password_hash'));

		if (!($customer->is_loaded && $token_valid)) {
				$this->message('Your link seems to be invalid.', 'error');
		} else {
			$token_expired = ($customer->val('customer_reset_password_expires') < z::mysqlTimestamp(time()));
			if ($token_expired) {
				$message = $this->t('Your activation link has expired. Ask for <a href="%s">new password</a>.', $this->url('reset-password'));
				$this->z->messages->error($message);
			} else {
				$customer->set('customer_state', CustomerModel::customer_state_active);
				$customer->set('customer_reset_password_hash', null);
				$customer->set('customer_reset_password_expires', null);
				$customer->save();
				$this->z->custauth->createSession($customer);
				$this->message('Your account was successfully activated.', 'success');
			}
		}
	}
