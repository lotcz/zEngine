<?php
	if (!$this->z->isModuleEnabled('newsletter')) {
		$this->showNotFoundView();
		return;
	}

	$this->setPageTitle('Unsubscribe from Newsletter');

	$token = z::get('token');
	$email = z::get('email');

	if (!(isset($token) && isset($email))) {
		$this->message('This page should only be accessed from link sent to your e-mail.', 'error');
	} else {
		$sub = new NewsletterSubscriptionModel($this->z->db);
		$sub->loadByEmail($email);
		if (!$sub->is_loaded) {
			$this->z->messages->add(sprintf($this->t('No subscription found for \'%s\''), $email), 'error');
		} elseif (z::verifyHash($email, $token)) {
			$sub->set('newsletter_subscription_active', 0);
			$sub->save();
			$this->z->messages->add(sprintf($this->t('Your e-mail address \'%s\' was successfully unsubscribed from newsletter.'), $email), 'success');
		} else {
			$this->message('Your link seems to be invalid.', 'error');
		}
	}
