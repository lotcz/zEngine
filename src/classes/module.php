<?php

class zModule {
	
	public $z = null;
	public $config = [];
	
	function __construct($z) {
		$this->z = $z;			
	}
	
	public function onEnabled() {		
	}
	
}