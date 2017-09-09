<?php

class IpFailedAttemptModel extends zModel {

	public $table_name = 'ip_failed_attempts';
	public $id_name = 'ip_failed_attempt_id';

	public function loadByIp($ip) {
		$filter['ip_failed_attempt_ip'] = $ip;
		$this->loadSingleFiltered($filter);
	}

	public function saveFailedAttempt($db) {
		$attempt = new IpFailedAttemptModel($db);
		$ip = $_SERVER['REMOTE_ADDR'];
		$attempt->loadByIp($ip);
		if (!$attempt->is_loaded) {
				$attempt->set('ip_failed_attempt_ip', $ip);
		}
		$attempt->set('ip_failed_attempt_count', $attempt->ival('ip_failed_attempt_count',0)+1);
		$attempt->set('ip_failed_attempt_last', time());
		$attempt->save();
	}

}
