<?php

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/classes/module.php';

class zEngine {
	
	public $app_dir = '';	
	public $modules = [];
		
	function __construct($app_dir = '') {
		$this->app_dir = $app_dir;
		
		$this->enableModule('core');
		$this->enableModule('errorlog');
	}

	public function enableModule($module_name) {
		require_once __DIR__ . "/modules/$module_name.php";
		$module_class = $module_name . 'Module';
		$module = new $module_class($this);
		$module_config_path = $this->app_dir . "config/$module_name.php";
		if (file_exists($module_config_path)) {
			$module->config = include $module_config_path;
		}		
		$module->onEnabled();		
		$this->modules[$module_name] = $module;
		$this->$module_name = $module;
		
	}

	public function moduleEnabled($module_name) {
		return isset($this->modules[$module_name]);
	}
		
	public function run() {
		$this->core->parseURL();
		
		//TO DO: if alias module is active, rewrite path here
		
		//now decide what will happen (choose controllers)
		$this->core->chooseControllers();
		
		// run controllers
		$this->core->runPageController();
		$this->core->runMainController();
		$this->core->runMasterController();
		
		//TO DO: close db if active
		
		//start rendering with master
		$this->core->renderMasterView();		
	}
	
	public function fatalError($message) {
		$this->errorlog->write($message);
	}
	
}