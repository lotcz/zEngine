<?php

require_once __DIR__ . '/../classes/model.php';

class AdminRoleModel extends zModel {

	const role_superuser = 1;
	const role_admin = 2;

	public $table_name = 'admin_role';

}
