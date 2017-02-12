<?php

class CurrencyModel extends zModel {
	
	public $table_name = 'currencies';
	public $id_name = 'currency_id';
			
	public function convert($price) {
		return parseFloat($price) / $this->fval('currency_value');
	}
		
}