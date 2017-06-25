<?php
	require_once $home_dir . 'models/customer.m.php';
	require_once $home_dir . 'models/custsess.m.php';
	
	$sessions = CustomerSession::Select(
		/* db */		$db, 
		/* table */		'customer_sessions', 
		/* where */		'customer_session_expires <= ?',
		/* bindings */	[SqlQuery::mysqlTimestamp(time())],
		/* types */		's',
		/* paging */	null,
		/* orderby */	null
	);
	
	foreach ($sessions as $session) {
		$customer = new Customer($db, $session->val('customer_session_customer_id'));
		$session->deleteById();
		if ($customer->val('customer_anonymous')) {
			$customer->deleteById();
		}		
	}
	
	echo 'Sessions cleared.';