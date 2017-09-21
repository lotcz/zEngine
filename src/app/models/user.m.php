<?php

class UserModel extends zModel {

	public $table_name = 'users';
	public $id_name = 'user_id';
	public $permissions = [];

	public function loadByLoginOrEmail($loginoremail) {
		$where = 'user_login = ? OR user_email = ?';
		$bindings = [$loginoremail, $loginoremail];
		$types = 'ss';
		$this->loadSingleFiltered($where, $bindings, $types);
	}

	public function loadPermissions() {
		$permissions = zModel::select($this->db, 'viewPermissionsByUser', 'user_role_user_id = ?', [ $this->val('user_id') ]);
		$this->permissions = [];
		foreach ($permissions as $permission) {
			$this->permissions[] = $permission->val('permission_name');
		}
	}

	public function hasPermission($perm_name) {
		if (!isset($this->permissions)) {
			$this->loadPermissions();
		}
		return in_array($perm_name, $this->permissions);
	}

}
