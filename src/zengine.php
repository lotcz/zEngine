<?php

require_once __DIR__ . '/app/classes/z.php';
require_once __DIR__ . '/app/classes/module.php';

/**
* zEngine himself - this is the main class of zEngine.
* All you have to do is create instance of this class and call method run().
*/
class zEngine {

	public $version = 3.5;
  public $app_dir = '';
	public $modules = [];

	function __construct($app_dir = 'app/', $modules = ['core']) {
		$this->app_dir = $app_dir;
		foreach ($modules as $module_name) {
			$this->enableModule($module_name);
		}
	}

	/**
	* Enables zEngine module. This will also call module's onEnabled method if defined.
	*/
	public function enableModule($module_name) {
		try {
			if (!$this->isModuleEnabled($module_name)) {
				require_once __DIR__ . "/app/modules/$module_name.php";
				$module_class = $module_name . 'Module';
				$module = new $module_class($this);
				$module->name = $module_name;

				// look for app's own module config file
				$module_config_path = $this->app_dir . "config/$module_name.php";
				if (file_exists($module_config_path)) {
					$module->config = include $module_config_path;
				} else {
					// look for zEngine's default module config file
					$module_config_path = __DIR__ . "/app/config/$module_name.php";
					if (file_exists($module_config_path)) {
						$module->config = include $module_config_path;
					}
				}

				// enable dependency modules
				foreach ($module->depends_on as $dependecy_module_name) {
					$this->enableModule($dependecy_module_name);
				}

				// add to modules array
				$this->modules[$module_name] = $module;

				// add module reference to $z directly
				$this->$module_name = $module;

				// run onEnabled method
				if (method_exists($module, 'onEnabled')) {
					$module->onEnabled();
				}
			}
		} catch (Exception $e) {
			$this->fatalError(sprintf('Error when enabling module %s: %s', $module_name, $e->getMessage()));
		}
	}

	/**
	* Returns true if module is enabled.
	*/
	public function isModuleEnabled($module_name) {
		return isset($this->modules[$module_name]);
	}

	/**
	* Returns true if application is in debug mode.
	*/
	public function isDebugMode() {
		return $this->core->debug_mode;
	}

	/**
	* This is the entry point for zEngine application. Call this from index.php.
	*/
	public function run() {
		try {

			if (isset($_GET['path'])) {
				$this->core->parseURL($_GET['path']);
			}

			foreach ($this->modules as $module) {
				if (method_exists($module, 'onBeforeInit')) {
					$module->onBeforeInit();
				}
			}

			$this->core->chooseControllers();

			foreach ($this->modules as $module) {
				if (method_exists($module, 'onAfterInit')) {
					$module->onAfterInit();
				}
			}

			$this->core->runMasterController();
			$this->core->runMainController();
			$this->core->runPageController();

			foreach ($this->modules as $module) {
				if (method_exists($module, 'onBeforeRender')) {
					$module->onBeforeRender();
				}
			}

			$this->core->renderMasterView();

			foreach ($this->modules as $module) {
				if (method_exists($module, 'onAfterRender')) {
					$module->onAfterRender();
				}
			}
		} catch (Exception $e) {
			$this->fatalError(sprintf('Unrecoverable error on page \'%s\': %s', $this->core->raw_path, $e->getMessage()));
		}
	}

	/**
	* Handles unrecoverable application error. This is called automatically if there is unhandled exception raised anywhere in the application.
	*/
	public function fatalError($error_message) {
		if ($this->isDebugMode()) {
			http_response_code(500);
			die($error_message);
		} else {
			$this->errorlog->write($error_message);
			$this->core->redirect($this->core->error_page);
		}
	}

}
