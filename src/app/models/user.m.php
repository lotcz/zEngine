<?php

require_once __DIR__ . '/../classes/model.php';
require_once __DIR__ . '/user_role.m.php';

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

	private $role;

	public function loadByLoginOrEmail($loginoremail) {
		$where = 'user_login = ? OR user_email = ?';
		$bindings = [$loginoremail, $loginoremail];
		$types =  [PDO::PARAM_STR, PDO::PARAM_STR];
		$this->loadSingle($where, $bindings, $types);
	}

	public function getLabel() {
		return $this->val('user_login', $this->val('user_email'));
	}

	public function isAnonymous(): bool {
		return ($this->ival('user_state') === Self::user_state_anonymous);
	}

	public function isWaitingForPaswordReset() {
		return ($this->ival('user_state') === Self::user_state_waiting_for_password_reset);
	}

	public function isWaitingForActivation(): bool {
		return ($this->ival('user_state') === Self::user_state_waiting_for_activation);
	}

	public function resetTokenExpired(): bool {
		return $this->val('user_reset_password_expires') < z::mysqlTimestamp(time());
	}

	public function isActive() {
		return ($this->ival('user_state') === Self::user_state_active);
	}

	public function isDeactivated() {
		return ($this->ival('user_state') === Self::user_state_deactivated);
	}

	public function getStatusLabel() {
		return Self::getUserStatusLabel($this->ival('user_state'));
	}

	public static function getUserStatusLabel($state) {
		switch (z::parseInt($state)) {
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

	public static function getStateOptions() {
		$options = [];
		$cancelled = new zModel();
		$cancelled->set('value', Self::user_state_cancelled);
		$options[] = $cancelled;
		$disabled = new zModel();
		$disabled->set('value', Self::user_state_deactivated);
		$options[] = $disabled;
		$anonymous = new zModel();
		$anonymous->set('value', Self::user_state_anonymous);
		$options[] = $anonymous;
		$activation = new zModel();
		$activation->set('value', Self::user_state_waiting_for_activation);
		$options[] = $activation;
		$reset = new zModel();
		$reset->set('value', Self::user_state_waiting_for_password_reset);
		$options[] = $reset;
		$active = new zModel();
		$active->set('value', Self::user_state_active);
		$options[] = $active;

		foreach ($options as $option) {
			$option->set('label', Self::getUserStatusLabel($option->ival('value')));
		}

		return $options;
	}

	public function getJson() {
		$json = parent::getJson();
		unset($json->password_hash);
		unset($json->reset_password_hash);
		return $json;
	}

	public function getRole() {
		if ($this->role === null) {
			$this->role = new UserRoleModel($this->db, $this->ival('user_user_role_id'));
		}
		return $this->role;
	}

	public function hasRole($role) {
		return $this->ival('user_user_role_id') == $role;
	}

	/**
	 * Return true if user has any of provided roles.
	 * If no roles are provided return true for any internal user.
	 */
	public function hasAnyRole($roles = null) {
		if ($roles === null || count($roles) === 0) {
			return !$this->isExternal();
		}

		for ($i = 0, $max = count($roles); $i < $max; $i++) {
			if ($this->hasRole($roles[$i])) {
				return true;
			}
		}

		return false;
	}

	public function isExternal() {
		return $this->hasRole(UserRoleModel::role_external) || $this->getRole()->isExternal();
	}
}
