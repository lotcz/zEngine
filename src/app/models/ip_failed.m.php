<?php

/**
* Model representing failed login attempt for specific IP address.
*/
class IpFailedAttemptModel extends zModel {

	public $table_name = 'ip_failed_attempt';

	public function loadByIp($ip) {
		$this->loadSingle('ip_failed_attempt_ip = ?', [$ip], [PDO::PARAM_STR]);
	}

}
