<?php

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/classes/module.php';

class zEngine {
	
	public $app_dir = '';	
	public $modules = [];
		
	function __construct($app_dir = 'app/') {
		$this->app_dir = $app_dir;		
		$this->enableModule('core');		
	}

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
			die(sprintf('Error when enabling module %s: %s', $module_name, $e->getMessage()));
		}
	}

	public function moduleEnabled($module_name) {
		return isset($this->modules[$module_name]);
	}
		
	public function isDebugMode() {
		return $this->core->debug_mode;
	}
	
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
			if ($this->isDebugMode()) {
				http_response_code(500);
				die($e->getMessage());
			} else {
				$this->errorlog->write(sprintf('Unrecoverable error on page\'%s\': %s', $this->core->raw_path, $e->getMessage()));
				$this->core->redirect($this->core->error_page);
			}
		}
	}
		
}