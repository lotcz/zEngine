<?php

require_once __DIR__ . '/../classes/paging.php';
require_once __DIR__ . '/../classes/tables.php';

class tablesModule extends zModule {
	
	public function onEnabled() {
		$this->requireModule('mysql');
	}
}