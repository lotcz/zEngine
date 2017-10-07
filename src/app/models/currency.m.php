<?php

class CurrencyModel extends zModel {

	public $table_name = 'currencies';
	public $id_name = 'currency_id';

	public function convert($price) {
		return z::parseFloat($price) / $this->fval('currency_value');
	}

	public function format($price) {
		return sprintf($this->val('currency_format'), $price);
	}

}
