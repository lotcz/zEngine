<?php

require_once __DIR__ . '/../classes/model.php';

class OrderStateModel extends zModel {

	const STATE_NEW = 1;
	const STATE_PROCESSING = 2;
	const STATE_REOPENED = 3;
	const STATE_CLOSED = 4;
	const STATE_CANCELLED = 5;

	public $table_name = 'order_state';

}
