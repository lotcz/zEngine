<?php

class appModule extends zModule {

	public $version = 0;
	public $require_z_version = 0;

	public $modules = [];

	public function onEnabled() {
		$this->requireConfig();
		$this->version = $this->getConfigValue('version', $this->version);
		$this->require_z_version = $this->getConfigValue('require_z_version', $this->require_z_version);

		if ($this->z->version < $this->require_z_version) {
			throw new Exception(sprintf('zEngine version %s is too old. Application requires at least version %s.', $this->z->version, $this->require_z_version));
		}
		
		$this->modules = $this->getConfigValue('modules', $this->modules);
		foreach ($this->modules as $module_name) {
			$this->requireModule($module_name);
		}
	}

}
