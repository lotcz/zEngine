<?php

/**
* Base class for all zEngine modules.
*/
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
	
	public function getConfigValue($attr_name, $default = null) {
		$ret_val = null;
		if (isset($this->config) && isset($this->config[$attr_name])) {
			$ret_val = $this->config[$attr_name];
		}
		if ($ret_val == null) {
			$ret_val = $default;
		}
		return $ret_val;
	}
		
}