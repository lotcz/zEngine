<?php
	$this->requireModule('auth');

	$sessions = UserSessionModel::select(
		/* db */		$this->z->db,
		/* table */		'user_session',
		/* where */		'user_session_expires <= ?',
		/* orderby */	null,
		/* limit */	null,
		/* bindings */	[z::mysqlTimestamp(time())],
		/* types */		[PDO::PARAM_INT]
	);

	foreach ($sessions as $session) {
		$user = new UserModel($this->z->db, $session->ival('user_session_user_id'));
		$session->delete();
		if ($user->isAnonymous()) {
			$user->delete();
		}
	}

	echo 'Sessions cleared.';
