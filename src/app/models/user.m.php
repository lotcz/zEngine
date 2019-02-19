<?php

require_once __DIR__ . '/../classes/model.php';

class UserModel extends zModel {

	/*
		STATES
	*/
	const user_state_anonymous = 0;
	const user_state_waiting_for_activation = 1;
	const user_state_active = 2;
	const user_state_waiting_for_password_reset = 3;
	const user_state_cancelled = 4;
	const user_state_deactivated = 5;

	public function loadByLoginOrEmail($loginoremail) {
		$where = 'user_login = ? OR user_email = ?';
		$bindings = [$loginoremail, $loginoremail];
		$types =  [PDO::PARAM_STR, PDO::PARAM_STR];
		$this->loadSingle($where, $bindings, $types);
	}

	public function getLabel() {
		return $this->val('user_login', $this->val('user_email'));
	}

	public function isAnonymous() {
		return ($this->ival('user_state') == Self::user_state_anonymous);
	}

	public function isWaitingForActivation() {
		return ($this->ival('user_state') == Self::user_state_waiting_for_activation);
	}

	public function isActive() {
		return ($this->ival('user_state') == Self::user_state_active);
	}

	public function getStatusLabel() {
		return Self::getUserStatusLabel($this->ival('user_state'));
	}

	public static function getUserStatusLabel($state) {
		switch ($state) {
			case Self::user_state_anonymous:
				return 'Anonymous';
			break;
			case Self::user_state_waiting_for_activation:
				return 'Waiting for activation';
			break;
			case Self::user_state_active:
				return 'Active';
			break;
			case Self::user_state_waiting_for_password_reset:
				return 'Waiting for password reset';
			break;
			case Self::user_state_cancelled:
				return 'Cancelled';
			break;
			case Self::user_state_deactivated:
				return 'Deactivated';
			break;
			default:
				return 'Unknown';
		}
	}

}
