<?php
	if (!$this->z->isModuleEnabled('newsletter')) {
		$this->showNotFoundView();
		return;
	}

	$this->setPageTitle('Unsubscribe From Newsletter');

	$token = z::get('token');
	$email = z::get('email');

	if (!(isset($token) && isset($email))) {
		$this->message('This page should only be accessed from link sent to your e-mail.', 'error');
	} else {
		$sub = new NewsletterSubscriptionModel($this->z->db);
		$sub->loadByEmail($email);
		$token_valid = z::verifyHash($email, $token);

		if (!($sub->is_loaded && $token_valid)) {
			$this->message('Your link seems to be invalid.', 'error');
		} else {
			$sub->set('newsletter_subscription_active', 0);
			$this->message('Your e-mail address was successfully unsubscribed from newsletter.', 'info');
		}
	}
