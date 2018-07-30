<?php
	$this->setPageTitle('Account activation');

	$activation_token = z::get('activation_token');
	$customer_email = z::get('email');

	if (isset($reset_token) && isset($customer_email)) {
		$customer = new CustomerModel($this->db);
		$customer->loadByEmail($customer_email);

		$token_not_expired = ($customer->val('customer_reset_password_expires') > zSqlQuery::mysqlTimestamp(time()));
		$token_valid = custauthModule::$activation_token($reset_token, $customer->val('customer_reset_password_hash'));

		if ($customer->is_loaded && $token_not_expired && $token_valid) {
				$customer->set('customer_password_hash', $this->z->custauth->hashPassword($password));
				$customer->set('customer_reset_password_hash', null);
				$customer->set('customer_reset_password_expires', null);
				$customer->save();
				$this->message('Your account was successfully activated.', 'success');
		} else {
			$this->message('Your link seems to be invalid.', 'error');
		}
	} else {
		$this->message('This page should only be accessed from link sent to your e-mail.', 'error');
	}
