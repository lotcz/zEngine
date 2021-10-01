<?php

/**
* Model representing banned IP addresses.
*/
class BannedIpModel extends zModel {

	public $table_name = 'banned_ip';

	public function loadByIp($ip) {
		$this->loadSingle('banned_ip_ip = ?', [$ip], [PDO::PARAM_STR]);
	}

	/**
	 * Save banned IP if doesn't exist yet.
	 * @param  [type] $db [description]
	 * @param  [type] $ip [description]
	 * @return [type]     [description]
	 */
	public static function saveBannedIp($db, $ip) {
		$banned_ip = new BannedIpModel($db);
		$banned_ip->loadByIp($ip);
		if (!$banned_ip->is_loaded) {
			$banned_ip->set('banned_ip_ip', $ip);
			$banned_ip->save();
		}
	}

}
