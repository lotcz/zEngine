<?php
	$this->requireModule('auth');

	$sessions = UserSession::select(
		/* db */		$this->z->db,
		/* table */		'user_sessions',
		/* where */		'user_session_expires <= ?',
		/* bindings */	[SqlQuery::mysqlTimestamp(time())],
		/* types */		null,
		/* paging */	null,
		/* orderby */	null
	);

	foreach ($sessions as $session) {
		$user = new UserModel($this->z->db, $session->ival('user_session_user_id'));
		$session->delete();
		if ($user->isAnonymous()) {
			$user->delete();
		}
	}

	echo 'Sessions cleared.';
