<?php

class DeliveryTypeModel extends zModel {
	
	public $table_name = 'delivery_types';
	public $id_name = 'delivery_type_id';
	
	public function loadAllowedPaymentTypes() {
		$this->allowed_payment_types = ModelBase::select($this->db, 'viewAllowedPaymentTypes', 'allowed_payment_type_delivery_type_id = ?', [ $this->val('delivery_type_id') ]);
		return $this->allowed_payment_types;
	}	
	
	static function getDefault($delivery_types) {
		return Self::find($delivery_types, 'delivery_type_is_default', 1);
	}
	
	static function loadDefault($db) {
		$d = new DeliveryType($db);
		$d->loadSingleFiltered('delivery_type_is_default = ?', 1);
		return $d;
	}
		
}