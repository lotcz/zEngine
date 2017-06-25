<?php

class OrderStateModel extends zModel {
	
	public $table_name = 'order_states';
	public $id_name = 'order_state_id';	
	
	static $new = 1;
	static $processing = 2;
	static $reopened = 3;
	static $closed = 4;
	static $cancelled = 5;
	
}