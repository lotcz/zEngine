<?php

/**
* Module that simplifies application versioning and automatic module loading.
*/
class appModule extends zModule {

	public $version = 0;
	public $require_z_version = 0;

	public $modules = [];

	public $includes = [];
	
	public function onEnabled() {
		$this->requireConfig();
		$this->version = $this->getConfigValue('version', $this->version);
		$this->require_z_version = $this->getConfigValue('require_z_version', $this->require_z_version);

		if ($this->z->version < $this->require_z_version) {
			throw new Exception(sprintf('zEngine version %s is too old. Application requires at least version %s.', $this->z->version, $this->require_z_version));
		}
		
		// activate default modules
		$this->modules = $this->getConfigValue('modules', $this->modules);
		foreach ($this->modules as $module_name) {
			$this->requireModule($module_name);
		}
		
		// process default includes
		$this->includes = $this->getConfigValue('includes', $this->includes);
		foreach ($this->includes as $include) {
			$this->z->core->addToIncludes(($include[1]) ? $include[0] : $this->z->core->url($include[0]), $include[2], $include[3]);
		}
	}

}