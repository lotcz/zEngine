<?php

class adminModule extends zModule {
	
	private $db = null;
	
	public function onEnabled() {
		$this->requireModule('auth');
		$this->requireModule('forms');
		$this->requireModule('tables');
		$this->db = $this->z->core->db;
	}
	
}