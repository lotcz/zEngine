<?php

require_once __DIR__ . '/../models/newsletter_subscription.m.php';

/**
* Module that handles email newsletters.
*/
class newsletterModule extends zModule {

	public $depends_on = ['db', 'emails', 'forms'];

	public function addSubscription($email) {
		$subs = new NewsletterSubscriptionModel($this->z->db);
		$mail = z::trimSpecial($email);
		if (!zForm::validate_email($email)) {
			return false;
		}
		$subs->loadByEmail($email);
		if ($subs->is_loaded) {
			return false;
		} else {
			$subs->set('newsletter_subscription_email', $email);
			$subs->save();
			return $subs;
		}
	}

	public function importSubscriptions($str) {
		$arr = z::splitString($str, [' ', "\r\n", "\n", ',', ';']);
		$total = count($arr);
		$imported = 0;
		foreach ($arr as $email) {
			if ($this->addSubscription($email)) {
				$imported += 1;
			}
		}
		return [$imported, $total];
	}
	
	public function createUnsubscribeLink($email) {
		return $this->z->url(sprintf('newsletter-unsubscribe?email=%s&token=%s', $email, z::createHash($email)));
	}

}
