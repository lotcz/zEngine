<?php

class DeliveryTypeModel extends zModel {

	public function loadAllowedPaymentTypes() {
		$this->allowed_payment_types = zModel::select($this->z->db, 'viewAllowedPaymentTypes', 'allowed_payment_type_delivery_type_id = ?', [ $this->val('delivery_type_id') ]);
		return $this->allowed_payment_types;
	}

	static function getDefault($delivery_types) {
		return Self::find($delivery_types, 'delivery_type_is_default', 1);
	}

}
