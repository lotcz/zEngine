<?php

class coreModule extends zModule {
	
	public $base_url = '';
	public $app_dir = '';
	
	public $data = [
		'page_title' => 'zEngine',
		'site_title' => 'zEngine'
	];
	
	public $master = 'default';
	public $main = 'default';
	public $page = 'default'; 
	
	public $controllers = ['master' => null, 'main' => null, 'page' => null];	
	public $templates = ['master' => null, 'main' => null, 'page' => null];
	
	public $path = [];
	public $raw_path = '';
	
	public function onEnabled() {
		$this->base_url = $this->config['base_url'];
		$this->app_dir = $this->z->app_dir;
	}
	
	/*
		RENDERING
	*/
			
	public function parseURL() {		
		if (isset($_GET['path'])) {
			$this->path = explode('/', trimSlashes(strtolower($_GET['path'])));
			$this->raw_path = implode('/', $this->path);
		}
	}
	
	public function chooseControllers() {
		$path_items = count($this->path);
		if ($path_items > 0) {
			if ($path_items == 1) {
				$this->page = $this->path[0];
			} else {				
				$this->master = $this->path[0];				
				$this->main = $this->path[1];				
				$this->page = $this->path[2];
			}
		}
	}
	
	public function renderView($type = 'page') {
		if (!isset($this->templates[$type])) {
			 $this->templates[$type] = $this->$type;
		}
		$template_path = $this->app_dir . "views/$type/" .  $this->templates[$type] . '.v.php';
		if (file_exists($template_path)) {
			include $template_path;
		} else {
			$this->z->fatalError("Template for $type view not found: $template_path!");
		}
	}
	
	public function renderMasterView() {
		$this->renderView('master');
	}
	
	public function renderMainView() {
		$this->renderView('main');
	}
	
	public function renderPageView() {
		$this->renderView();
	}
	
	public function runController($type = 'page') {
		if (!isset($this->controllers[$type])) {
			 $this->controllers[$type] = $this->$type;
		}
		$controller_path = $this->app_dir . "controllers/$type/" . $this->controllers[$type] . '.c.php';
		if (file_exists($controller_path)) {
			include $controller_path;
		}
	}
	
	public function runMasterController() {
		$this->runController('master');
	}
	
	public function runMainController() {
		$this->runController('main');
	}
	
	public function runPageController() {
		$this->runController();
	}
	
	public function url($link = '', $r = null) {
		$url = $this->base_url . '/' . $link;
		if (isset($ret)) {
			$url .= '?r=' . $r;
		}		
		return $url;
	}
	
	public function t($s) {
		if ($this->z->moduleEnabled('i18n')) {
			$t = $this->z->i18n->translate($s);
			if (func_num_args() > 1) {
				$args = func_get_args();
				array_shift($args);
				array_unshift($args, $t);
				return call_user_func_array('sprintf', $args);
			} else {
				return $t;
			}
		} else {
			return $s;
		}
	}
	
	public function formatMoney($price) {
		if ($this->z->moduleEnabled('i18n')) {
			return $this->z->i18n->formatMoney($price);
		} else {
			return $price;
		}
	}
	public function formatDecimal($number, $decimals = 2) {
		if ($this->z->moduleEnabled('i18n')) {
			return $this->z->i18n->selected_language->formatDecimal($number, $decimals);
		} else {
			return $number;
		}
	}
	
	public function formatInteger($number) {
		if ($this->z->moduleEnabled('i18n')) {
			return $this->z->i18n->selected_language->formatInteger($number);
		} else {
			return $number;
		}
	}
	
	public function formatDate($date) {
		if ($this->z->moduleEnabled('i18n')) {
			return $this->z->i18n->selected_language->formatDate($date);
		} else {
			return $number;
		}
	}
	
	public function formatDatetime($date) {
		if ($this->z->moduleEnabled('i18n')) {
			return $this->z->i18n->selected_language->formatDatetime($date);
		} else {
			return $number;
		}
	}
	
}