<?php

class ProductModel extends zModel {
	
	public $table_name = 'products';
	public $id_name = 'product_id';
	
	public function loadByExtId($id) {
		$filter = 'product_ext_id = ?';
		$this->loadSingleFiltered($filter, [$id]);		
	}
	
	public function loadVariants() {
		$this->variants = zModel::select($this->db, 'product_variants', 'product_variant_product_id = ?', [ $this->val('product_id') ]);
	}
	
	static function loadCart($db, $customer_id) {
		return Self::select(
		/* db */		$db, 
		/* table */		'viewProductsInCart', 
		/* where */		'cart_customer_id = ?',
		/* bindings */	[ intval($customer_id) ],
		/* types */		'i',
		/* paging */	null,
		/* orderby */	'product_name'
		);		
	}
	
	public function getAliasUrl() {		
		return $this->val('product_name');		
	}
	
	public function getViewPath() {
		return 'default/default/product/' . $this->val('product_id');
	}
	
	static function getSortingItems() {
		return [
			'sortby_Popularity' => 'product_stock DESC',
			'sortby_Alphabet' => 'product_name ASC',
			'sortby_Alphabet_DESC' => 'product_name DESC',
			'sortby_Price' => 'product_price ASC',
			'sortby_Price_DESC' => 'product_price DESC'					
		];
	}
	
}