<?php

require_once __DIR__ . '/classes/z.php';
require_once __DIR__ . '/classes/module.php';

/**
* zEngine himself - this is the main class of zEngine.
* All you have to do is create instance of this class and call method run().
*/
class zEngine {

	public $app_dir = '';
	public $modules = [];
	public $version = 1.3;

	function __construct($app_dir = 'app/') {
		$this->app_dir = $app_dir;
		$this->enableModule('core');
	}

	/**
	* Enables zEngine module. This will also call module's onEnabled method if defined.
	*/
	public function enableModule($module_name) {
		try {
			if (!$this->moduleEnabled($module_name)) {
				require_once __DIR__ . "/modules/$module_name.php";
				$module_class = $module_name . 'Module';
				$module = new $module_class($this);
				$module->name = $module_name;

				$module_config_path = $this->app_dir . "config/$module_name.php";
				if (file_exists($module_config_path)) {
					$module->config = include $module_config_path;
				}

				$this->modules[$module_name] = $module;
				$this->$module_name = $module;

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
	public function moduleEnabled($module_name) {
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
				if (method_exists($module, 'onInit')) {
					$module->onInit();
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
