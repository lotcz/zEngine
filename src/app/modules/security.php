<?php

require_once __DIR__ . '/../models/ip_failed.m.php';
require_once __DIR__ . '/../models/banned_ip.m.php';

/**
* Module that handles security needs.
  At the moment it is all about IP blocking.
*/
class securityModule extends zModule {

	public array $depends_on = ['db'];

	// if an IP exceeds this number of failed attempts, it will be banned
	public $max_failed_attempts = 100;

	// number of failed attempts will be reset after this period, leave empty for no reset
	// https://www.php.net/manual/en/dateinterval.createfromdatestring.php
	public $reset_failed_attempts_after;

	function onEnabled() {
		$this->max_failed_attempts = $this->getConfigValue('max_failed_attempts', $this->max_failed_attempts);
		$this->reset_failed_attempts_after = $this->getConfigValue('reset_failed_attempts_after');
	}

	public function onBeforeInit() {
		$ip = z::getClientIP();
		if ($this->isBannedIP($ip)) {
			http_response_code(403);
			die("Your IP address is forbidden to access this site!");
		}
	}

	/**
	 * Save failed attempt for current IP.
	 * Failed attempt refers to failed login, failed form submission or something else that might indicate an attack.
	 * @return [type] [description]
	 */
	public function saveFailedAttempt() {
		$attempt = new IpFailedAttemptModel($this->z->db);
		$ip = z::getClientIP();
		$attempt->loadByIp($ip);
		if (!$attempt->is_loaded) {
			$attempt->set('ip_failed_attempt_ip', $ip);
		}
		$attempt->set('ip_failed_attempt_count', $attempt->ival('ip_failed_attempt_count', 0) + 1);
		$attempt->set('ip_failed_attempt_last', z::mySqlDatetime(time()));
		$attempt->save();

		if ($attempt->ival('ip_failed_attempt_count') > $this->max_failed_attempts) {
			$this->banIP($ip);
		}
	}

	/**
	 * List given IP address as banned,
	 * @param  [type] $ip [description]
	 * @return [type]     [description]
	 */
	public function banIP($ip) {
		$banned_ip = new BannedIpModel($this->z->db);
		$banned_ip->loadByIp($ip);
		if (!$banned_ip->is_loaded) {
			$banned_ip->set('banned_ip_ip', $ip);
			$banned_ip->save();
		}
	}

	/**
	 * Checks if given IP address is banned.
	 * @param  [type] $ip [description]
	 * @return bool     [description]
	 */
	public function isBannedIP($ip) {
		$banned_ip = new BannedIpModel($this->z->db);
		$banned_ip->loadByIp($ip);
		return $banned_ip->is_loaded;
	}

	public function resetFailedAttempts() {
		if (empty($this->reset_failed_attempts_after)) return;
		$threshold = (new DateTime())->sub(new DateInterval($this->reset_failed_attempts_after));
		$this->z->db->executeDeleteQuery(
			IpFailedAttemptModel::getTableName(),
			'ip_failed_attempt_first <= ?',
			[z::mysqlDatetime($threshold)],
			[PDO::PARAM_STR]
		);
	}
}
