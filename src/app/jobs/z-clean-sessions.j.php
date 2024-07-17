<?php

	if (!$this->z->isModuleEnabled('auth')) {
		echo "Auth module disabled!" . PHP_EOL;
		die();
	}

	$sessions = UserSessionModel::select(
		$this->z->db, /* db */
		'user_session', /* table */
		'user_session_expires <= ?', /* where */
		null, /* orderby */
		null, /* limit */
		[z::mysqlTimestamp(time())], /* bindings */
		[PDO::PARAM_STR] /* types */
	);

	foreach ($sessions as $session) {
		$user = new UserModel($this->z->db, $session->ival('user_session_user_id'));
		$session->delete();
		if ($user->isAnonymous()) {
			$user->delete();
		}
	}

	$count = count($sessions);
	echo "Deleted <strong>$count</strong> old sessions." . PHP_EOL;
