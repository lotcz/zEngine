<?php

require_once __DIR__ . '/../classes/model.php';

class UserSessionModel extends zModel {

	public $table_name = 'user_session';

	static function setSessionExpiration($db, $session_id, $expires) {
		$session = new UserSessionModel($db);
		$session->set('user_session_id', $session_id);
		$session->set('user_session_expires', z::mysqlTimestamp($expires));
		$session->save();
	}

}
