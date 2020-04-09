<?php

/**
* Base class for all zEngine modules.
*/
class zModule {

	/**
	* Reference to zEngine that owns this module.
	*/
	public $z = null;

	/**
	* Short name of this module.
	* Must be identical with php file names. Important when loading config files etc.
	*/
	public $name = null;

	/**
	* Final config for this module merged from zEngine's default, app's default and local.
	*/
	public $config = null;

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
		$file_pattern = __DIR__ . '/../../../install/' . $this->name . '.%s';
		$install_script_file = '';
		$db_specific_file = sprintf($file_pattern, $this->z->db->connection_type);
		if (file_exists($db_specific_file)) {
			$install_script_file = $db_specific_file;
		} else {
			$install_script_file = sprintf($file_pattern, 'sql');
		}
		if (file_exists($install_script_file)) {
			$this->z->db->executeFile($install_script_file, $db_login, $db_password, $db_name);
		}
	}

}
