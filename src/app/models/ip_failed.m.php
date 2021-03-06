<?php

/**
* Model representing failed login attempt for specific IP address.
*/
class IpFailedAttemptModel extends zModel {

	public $table_name = 'ip_failed_attempt';

	public function loadByIp($ip) {
		$this->loadSingle('ip_failed_attempt_ip = ?', [$ip], [PDO::PARAM_STR]);
	}

	public static function saveFailedAttempt($db) {
		$attempt = new IpFailedAttemptModel($db);
		$ip = $_SERVER['REMOTE_ADDR'];
		$attempt->loadByIp($ip);
		if (!$attempt->is_loaded) {
				$attempt->set('ip_failed_attempt_ip', $ip);
		}
		$attempt->set('ip_failed_attempt_count', $attempt->ival('ip_failed_attempt_count', 0) + 1);
		$attempt->set('ip_failed_attempt_last', z::mySqlDatetime(time()));
		$attempt->save();
	}

}
