<?php
	require_once $home_dir . 'models/customer.m.php';
	require_once $home_dir . 'models/custsess.m.php';

	$sessions = CustomerSession::Select(
		/* db */		$this->z->db,
		/* table */		'customer_sessions',
		/* where */		'customer_session_expires <= ?',
		/* bindings */	[SqlQuery::mysqlTimestamp(time())],
		/* types */		's',
		/* paging */	null,
		/* orderby */	null
	);

	foreach ($sessions as $session) {
		$customer = new Customer($this->z->db, $session->val('customer_session_customer_id'));
		$session->delete();
		if ($customer->val('customer_anonymous')) {
			$customer->delete();
		}
	}

	echo 'Sessions cleared.';
