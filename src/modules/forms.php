<?php

require_once __DIR__ . '/../classes/forms.php';

class formsModule extends zModule {
	
	public function onEnabled() {
		$this->requireModule('mysql');
	}
}