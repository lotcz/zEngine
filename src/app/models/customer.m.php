<?php

class CustomerModel extends zModel {
	
	public $table_name = 'customers';
	public $id_name = 'customer_id';

	public function loadByEmail($email) {
		$where = 'customer_email = ?';
		$bindings = [$email];
		$types = 's';
		$this->loadSingle($where, $bindings, $types);		
	}
	
	public function getLabel() {
		if (strlen($this->val('customer_name') > 0)) {
			return $this->val('customer_name');
		} else {
			return $this->val('customer_email');
		}
	}
	
	/*
		STATE
	*/
	const customer_state_anonymous = 0;
	const customer_state_waiting_for_activation = 1;
	const customer_state_active = 2;
	const customer_state_waiting_for_password_reset = 3;
	const customer_state_cancelled = 4;
	const customer_state_deactivated = 5;
	
	public function isAnonymous() {
		return ($this->ival('customer_state') == Self::customer_state_anonymous);
	}
	
	public function isWaitingForActivation() {
		return ($this->ival('customer_state') == Self::customer_state_waiting_for_activation);
	}
	
	public function isActive() {
		return ($this->ival('customer_state') == Self::customer_state_active);
	}
	
	public function getStatusLabel() {
		return Self::getCustomerStatusLabel($this->ival('customer_state'));
	}
	
	public static function getCustomerStatusLabel($state) {
		switch ($state) {
			case Self::customer_state_anonymous:
				return 'Anonymous';
			break;
			case Self::customer_state_waiting_for_activation:
				return 'Waiting for activation';
			break;
			case Self::customer_state_active:
				return 'Active';
			break;
			case Self::customer_state_waiting_for_password_reset:
				return 'Waiting for password reset';
			break;
			case Self::customer_state_cancelled:
				return 'Cancelled';
			break;
			case Self::customer_state_deactivated:
				return 'Deactivated';
			break;
			default:
				return 'Unknown';
		}					
	}
	
}