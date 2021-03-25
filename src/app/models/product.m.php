<?php

require_once __DIR__ . '/../classes/model.php';

class ProductModel extends zModel {

	public function getPath() {
		return zModel::getProductPath($this->val('product_id'));
	}

	public static function getProductPath($id) {
		return sprintf('default/default/product/%d', $id);
	}

	public function loadPrevious() {
		$result = ProductModel::select(
			$this->db,
			ProductModel::getTableName(),
			'product_id < ? and product_product_category_id = ?',
			'product_id DESC',
			1,
			[$this->ival('product_id'), $this->ival('product_product_category_id')],
			[PDO::PARAM_INT, PDO::PARAM_INT]
		);

		if (count($result) > 0)
			return $result[0];

		// load last
		$result = ProductModel::select(
			$this->db,
			ProductModel::getTableName(),
			'product_product_category_id = ?',
			'product_id DESC',
			1,
			[$this->ival('product_product_category_id')],
			[PDO::PARAM_INT]
		);
		return $result[0];
	}

	public function loadNext() {
		$result = ProductModel::select(
			$this->db,
			ProductModel::getTableName(),
			'product_id > ? and product_product_category_id = ?',
			'product_id ASC',
			1,
			[$this->ival('product_id'), $this->ival('product_product_category_id')],
			[PDO::PARAM_INT, PDO::PARAM_INT]
		);

		if (count($result) > 0)
			return $result[0];

		// load fist
		$result = ProductModel::select(
			$this->db,
			ProductModel::getTableName(),
			'product_product_category_id = ?',
			'product_id ASC',
			1,
			[$this->ival('product_product_category_id')],
			[PDO::PARAM_INT]
		);
		return $result[0];
	}

}
