<?php

require_once __DIR__ . '/../models/newsletter_subscription.m.php';

/**
* Module that handles email newsletters.
*/
class newsletterModule extends zModule {

	public array $depends_on = ['db', 'emails', 'forms'];

	public function addSubscription($email) {
		$subs = new NewsletterSubscriptionModel($this->z->db);
		$mail = z::trimSpecial($email);
		if (!zForm::validate_email($email)) {
			$this->z->core->message(sprintf('<strong>"%s"</strong> is not a valid address!', $email), 'error');
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
		$this->z->core->message(sprintf('Imported %d from %d addresses.', $imported, $total), 'success');
		return [$imported, $total];
	}

	public function createUnsubscribeLink($email) {
		return $this->z->core->url(sprintf('newsletter-unsubscribe?email=%s&token=%s', $email, z::createHash($email)));
	}

	public function getActiveSubscriptions() {
		return NewsletterSubscriptionModel::select($this->z->db, 'newsletter_subscription', 'newsletter_subscription_active = 1');
	}

	public function cleanSubscriptionEmails() {
		$all_active = $this->getActiveSubscriptions();
		$deleted = 0;
		foreach ($all_active as $subscription) {
			$email = $subscription->val('newsletter_subscription_email');
			if (!zForm::validate_email($email)) {
				$trimmed = z::trim($email);
				if (zForm::validate_email($trimmed)) {
					$subscription->set('newsletter_subscription_email', $trimmed);
					$subscription->save();
					echo "Email \"$email\" trimmed to $trimmed" . PHP_EOL;
					continue;
				}

				$subscription->delete();
				$deleted += 1;
				echo "Deleted subscription <strong>\"$email\"</strong>." . PHP_EOL;
			}
		}
		return $deleted;
	}
}
