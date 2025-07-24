<?php

require_once __DIR__ . '/../models/email_evaluation_cache.m.php';
require_once __DIR__ . '/../classes/trainslate-async-job.php';

/**
* Module that evaluates email content using chatGPT API. This is used to determine undeliverable emails.
*/
class emailevalModule extends zModule {

	public array $depends_on = ['db', 'newsletter', 'chatgpt'];

	public array $also_install = [];

	public $cache_hashing_algorithm = 'md5';

	public $internal_cache = [];

	private $system_prompt = "You will receive body of an email that came as response to regular newsletter. " .
		"Your task will be to analyze the email and determine whether the recipient address should be removed from mailing list. " .
		"You will only reply with a single word that will indicate new status for recipient address. " .
		"Say 'invalid' when email body indicates that recipient or server doesn't exist, server is invalid or email was blocked by spam filter. " .
		"Say 'unsubscribed' when email body looks like it was written by someone who wishes to be removed from newsletter mailing list. " .
		"Say 'active' when email body doesn't indicate invalid address or server, for example vacation notification or some other type of email.";

	public function onEnabled() {
		$this->cache_hashing_algorithm = $this->getConfigValue('cache_hashing_algorithm', $this->cache_hashing_algorithm);
	}

	public function getCacheKeyHash(string $key): string {
		return hash($this->cache_hashing_algorithm, $key);
	}

	/*
	 * INTERNAL CACHE
	*/

	private function internalCacheExists($hash) {
		return isset($this->internal_cache[$hash]);
	}

	private function setInternalCache($hash, $value) {
		$this->internal_cache[$hash] = $value;
	}

	private function getInternalCache($hash) {
		if (!$this->internalCacheExists($hash)) {
			return null;
		}
		return $this->internal_cache[$hash];
	}

	/*
	 * DB CACHE
	*/

	private function loadCacheByHash(string $hash): ?EmailEvaluationCacheModel {
		$cached = new EmailEvaluationCacheModel($this->z->db);
		$cached->loadByHash($hash);
		return $cached->is_loaded ? $cached : null;
	}

	private function loadCacheByKey(string $key): ?EmailEvaluationCacheModel {
		return $this->loadCacheByHash($this->getCacheKeyHash($key));
	}

	/*
	 * EVALUATE
	 */

	private function performEmailEvaluation(string $text) {
		return $this->z->chatgpt->ask(
			[
				"Analyze following email body:",
				$text
			],
			$this->system_prompt
		);
	}

	public function evaluateEmail(?string $text) {
		// empty
		$text = z::trim($text);
		if (empty($text)) return 'active';

		// internal cache
		$hash = $this->getCacheKeyHash($text);
		$icache = $this->getInternalCache($hash);
		if (!empty($icache)) return $icache;

		// db cache
		$cached = $this->loadCacheByHash($hash);
		if (isset($cached)) {
			$state = $cached->val('email_evaluation_cache_state');
			$this->setInternalCache($hash, $state);
			return $state;
		}

		// perform AI translate
		$evaluated = z::trimSpecial($this->performEmailEvaluation($text));

		if (empty($evaluated)) {
			throw new Exception("AI evaluation was empty!" . PHP_EOL . $text);
		}

		if ($evaluated !== "active" && $evaluated !== "unsubscribed" && $evaluated !== "invalid") {
			throw new Exception(sprintf("AI evaluation returned unsupported value '%s'!", $evaluated) . PHP_EOL . $text);
		}

		// save to db cache
		$cached = new EmailEvaluationCacheModel($this->z->db);
		$cached->set('email_evaluation_cache_text', $text);
		$cached->set('email_evaluation_cache_hash', $hash);
		$cached->set('email_evaluation_cache_state', $evaluated);
		$cached->save();

		$this->setInternalCache($hash, $evaluated);
		return $evaluated;
	}

	public function processBouncedEmail($recipient, $body) {
		$subscription = new NewsletterSubscriptionModel($this->z->db);
		$subscription->loadByEmail($recipient);
		if ($subscription->val('newsletter_subscription_state') !== "active") {
			$this->z->errorlog->write("Email $recipient is already deactivated!");
			return;
		}

		$state = $this->evaluateEmail($body);

		if ($state === "active") {
			$this->z->errorlog->write("Email $recipient is still ok!");
			return;
		}

		$this->z->errorlog->write("Deactivating $recipient - $state!");
		$subscription->set('newsletter_subscription_state', $state);
		$subscription->set('newsletter_subscription_state_changed', z::mysqlTimestamp());
		$subscription->save();
	}

}
