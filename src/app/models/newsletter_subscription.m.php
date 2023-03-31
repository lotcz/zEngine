<?php

require_once __DIR__ . '/../classes/model.php';

class NewsletterSubscriptionModel extends zModel {

	public $table_name = 'newsletter_subscription';
	
	public function loadByEmail($email) {
		$filter = 'newsletter_subscription_email = ?';
		$this->loadSingle($filter, [$email], [PDO::PARAM_STR]);
	}

}
