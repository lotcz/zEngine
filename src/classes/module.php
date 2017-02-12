<?php

class zModule {
	
	public $z = null;
	public $config = null;
	public $name = null;
	
	function __construct($z) {
		$this->z = $z;			
	}
	
	public function requireConfig($attr_name = null) {
		if (!isset($this->config)) {
			throw new Exception(sprintf('Module %s requires config file.', $this->name));
		} elseif (isset($attr_name)) {
			if (!isset($this->config[$attr_name])) {
				throw new Exception(sprintf('Module %s requires config attribute %s to be set.', $this->name, $attr_name));
			}
		}
	}
	
	public function requireModule($module_name) {
		if (!$this->z->moduleEnabled($module_name)) {
			$this->z->enableModule($module_name);
		}
	}
	
}