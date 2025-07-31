<?php

require_once __DIR__ . '/../classes/model.php';

class UserRoleModel extends zModel {

	const role_superuser = 1;
	const role_admin = 2;

	const role_external = 3;

	public $table_name = 'user_role';

	public function isExternal() {
		return $this->bval('user_role_is_external');
	}

}
