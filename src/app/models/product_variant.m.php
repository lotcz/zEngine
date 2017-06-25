<?php

class ProductVariantModel extends zModel {
	
	public $table_name = 'product_variants';
	public $id_name = 'product_variant_id';
	
	public function load($product_id, $variant_name) {
		$filter = 'product_variant_product_id = ? AND product_variant_name = ?';
		$this->loadSingleFiltered($filter, [intval($product_id), $variant_name]);
	}

	public function loadByExtId($id) {
		$filter = 'product_variant_ext_id = ?';
		$this->loadSingleFiltered($filter, [$id]);		
	}
}