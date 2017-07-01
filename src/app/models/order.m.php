<?php

class OrderModel extends zModel {
	
	public $table_name = 'orders';
	public $id_name = 'order_id';	

	public function loadProducts() {
		$this->products = OrderProductModel::select(
		/* db */		$this->db, 
		/* table */		'viewOrderProducts', 
		/* where */		'order_product_order_id = ?',
		/* bindings */	[ $this->ival('order_id') ],
		/* types */		'i',
		/* paging */	null,
		/* orderby */	'order_product_name'
		);		
	}
	
	static function getNewOrderNumber($db)	{		
		$now = new DateTime();
		$year = $now->format("Y");
		$month = $now->format("m");
		$count = zSqlQuery::getRecordCount(
			$db, 
			'orders', 
			'where year(order_created) = ? and month(order_created) = ?', 
			[$year, $month], 
			$types = 'ii'
		);
		$number = $count + 1891;
		return intval("$year$month$number");
	}
}