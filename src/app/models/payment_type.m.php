<?php

class PaymentTypeModel extends zModel {
	
	public $table_name = 'payment_types';
	public $id_name = 'payment_type_id';
		
	static function getDefault($payment_types) {
		$def = Self::find($payment_types, 'payment_type_is_default', 1);
		if (isset($def)) {
			return $def;
		} else {
			return $payment_types[0];
		}
	}
	
	static function getAllowedPT($db) {
		 return ModelBase::select($db, 'allowed_payment_types');
	}
	
}