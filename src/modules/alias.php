<?php

require_once __DIR__ . '/../app/models/alias.m.php';

/**
* Module that handles page aliases.
*/
class aliasModule extends zModule {
	
	public function onEnabled() {
		$this->requireModule('mysql');
	}
	
	public function onInit() {
		$alias = new AliasModel($this->z->core->db);
		$alias->loadByUrl($this->z->core->raw_path);
		if ($alias->is_loaded) {
			$this->z->core->parseURL($alias->val('alias_path'));			
		}
	}
	
}