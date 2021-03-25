<?php

require_once __DIR__ . '/../classes/model.php';

class ProductModel extends zModel {

	public function getPath() {
		return zModel::getProductPath($this->val('product_id'));
	}

	public static function getProductPath($id) {
		return sprintf('default/default/product/%d', $id);
	}

}
