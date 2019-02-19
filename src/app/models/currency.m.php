<?php

require_once __DIR__ . '/../classes/model.php';

class CurrencyModel extends zModel {

	public function convert($price) {
		return z::parseFloat($price) / $this->fval('currency_value');
	}

	public function format($price) {
		return sprintf($this->val('currency_format'), $price);
	}

}
