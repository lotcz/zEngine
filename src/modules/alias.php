<?php

require_once __DIR__ . '/../app/models/alias.m.php';

class aliasModule extends zModule {
	
	private $db = null;
	
	public function onEnabled() {
		$this->requireModule('mysql');
		$this->db = $this->z->core->db;
	}
	
	public function onInit() {
		$this->db = $this->z->core->db;
	}
	
}