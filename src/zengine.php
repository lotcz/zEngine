<?php

require_once __DIR__ . '/app/classes/z.php';
require_once __DIR__ . '/app/classes/module.php';

/**
* zEngine himself - this is the main class of zEngine.
* All you have to do is create an instance of this class and call method run().
*/
class zEngine {

	public $version = 15.1;
	public $app_dir = '';
	public $modules = [];

	function __construct($app_dir = 'app/', $modules = []) {
		$this->app_dir = $app_dir;
		$this->enableModule('core');
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
				$app_modules_path = $this->app_dir . "modules/$module_name.php";
				if (file_exists($app_modules_path)) {
					require_once $app_modules_path;
				} else {
					$core_modules_path = __DIR__ . "/app/modules/$module_name.php";
					require_once $core_modules_path;
				}
				$module_class = $module_name . 'Module';
				$module = new $module_class($this);
				$module->name = $module_name;

				/**
				* zEngine's default config for this module.
				*/
				$default_config = [];

				/**
				* App's own default config for this module.
				*/
				$app_config = [];

				/**
				* Local config for this module specific to deployment.
				*/
				$local_config = [];

				// look for zEngine's default module config file
				$default_config_path = __DIR__ . "/app/config/$module_name.php";
				if (file_exists($default_config_path)) {
					$default_config = include $default_config_path;
				}

				// look for app's own default module config file
				$app_config_path = $this->app_dir . "config/$module_name.php";
				if (file_exists($app_config_path)) {
					$app_config = include $app_config_path;
				}

				// look for app's local module config file
				$local_config_path = $this->app_dir . "config/local/$module_name.php";
				if (file_exists($local_config_path)) {
					$local_config = include $local_config_path;
				}

				$module->config = z::mergeAssocArrays(z::mergeAssocArrays($default_config, $app_config), $local_config);

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

			if (empty($this->core->findViewTemplate('master'))) {
				if ($this->isDebugMode()) {
					$view_name = $this->core->getViewName('master');
					$this->fatalError("Template master file <strong>$view_name</strong> not found!");
				} else {
					$this->core->setMasterView('default');
				}
			}

			$this->core->runMainController();

			if ($this->core->require_main_view) {
				if (empty($this->core->findViewTemplate('main'))) {
					if ($this->isDebugMode()) {
						$view_name = $this->core->getViewName('main');
						$this->fatalError("Template main file <strong>$view_name</strong> not found!");
					} else {
						$this->core->setMainView('default');
					}
				}
			}

			$this->core->runPageController();

			if ($this->core->require_page_view) {
				if (empty($this->core->findViewTemplate('page'))) {
					if ($this->isDebugMode()) {
						$view_name = $this->core->getViewName('page');
						$this->fatalError("Template page file <strong>$view_name</strong> not found!");
					} else {
						$this->core->setPageController($this->core->not_found_page);
						$this->core->runPageController();
					}
				}
			}

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
			$this->fatalError(sprintf('Unrecoverable exception on page \'%s\': %s', $this->core->raw_path, $e->getMessage()));
		}
	}

	/**
	* Handles unrecoverable application error.
	* This is called when there is an unhandled exception raised anywhere in the application.
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
