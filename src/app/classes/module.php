<?php

/**
* Base class for all zEngine modules.
*/
class zModule {

	public $z = null;
	public $config = null;
	public $name = null;

	/**
	* Array of module names that this module depends on.
	* These modules will be installed on installAllModules command and always enabled when this module is enabled.
	*/
	public $depends_on = [];

	/**
	* Array of module names that must also be installed.
	* This can be modules that are activated only sometimes.
	* These modules will be also installed on installAllModules command, but not automatically enabled.
	*/
	public $also_install = [];

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
		$this->z->enableModule($module_name);
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

	public function install($db_login = null, $db_password = null, $db_name = null) {
		$sql_file = __DIR__ . '/../../../install/' . $this->name . '.sql';
		if (file_exists($sql_file)) {
			$this->z->db->executeFile($sql_file, $db_login, $db_password, $db_name);
		}
	}

}
