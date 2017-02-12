<?php

class coreModule extends zModule {
	
	public $app_dir = '';
	public $base_url = '';	
	public $debug_mode = false;
	public $error_page = 'error.html';
	
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
		$this->app_dir = $this->z->app_dir;
		$this->base_url = $this->config['base_url'];
		$this->debug_mode = $this->config['debug_mode'];
		$this->error_page = $this->config['error_page'];		
	}
	
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
	
	/*
		HELPERS
	*/
	
	public function redirect($url, $statusCode = 303) {
		header('Location: ' . trimSlashes($this->base_url) . '/' . trimSlashes($url), true, $statusCode);
		die();
	}	
	
	public function url($link = '', $r = null) {
		$url = $this->base_url . '/' . $link;
		if (isset($ret)) {
			$url .= '?r=' . $r;
		}		
		return $url;
	}
	
	public function getLink($path, $title, $css = '', $ret = null) {
		return sprintf('<a href="%s" class="%s">%s</a>', $this->url($path, $ret), $css, $this->t($title));
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
	
	public function isAuth() {
		return $this->z->auth->isAuth();
	}
	
	public function getUser() {
		return $this->z->auth->user;
	}
	
	public function isCustAuth() {
		return $this->z->custauth->isAuth();
	}
	
	public function getCustomer() {
		return $this->z->custauth->customer;
	}
	
	public function message($text, $type = 'info') {
		$this->z->messages->add($text, $type);
	}
	
	public function formatMoney($price) {
		if ($this->z->moduleEnabled('i18n')) {
			return $this->z->i18n->formatMoney($price);
		} else {
			return $price;
		}
	}
	
	public function convertMoney($price) {
		if ($this->z->moduleEnabled('i18n')) {
			return $this->z->i18n->convertMoney($price);
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
	
		
	/*
		RENDERING
	*/	
			
	public function renderView($type = 'page') {
		if (!isset($this->templates[$type])) {
			 $this->templates[$type] = $this->$type;
		}
		$template_path = $this->app_dir . "views/$type/" .  $this->templates[$type] . '.v.php';
		if (file_exists($template_path)) {
			include $template_path;
		} else {
			throw new Exception("Template for $type view not found: $template_path!");
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
	
	public function renderPartialView($partial_name) {
		$template_path = $this->app_dir . 'views/partial/' .  $partial_name . '.v.php';
		if (file_exists($template_path)) {
			include $template_path;
		} else {
			throw new Exception("Template for partial view $partial_name not found: $template_path!");
		}
	}
	
	public function renderMessages() {
		$this->z->messages->render();
	}
		
	public function renderLink($href, $title, $css = '', $ret = null) {
		echo $this->getLink($href, $title, $css, $ret);
	}
	
	public function renderImage($src, $alt, $css = '') {;		
		echo sprintf('<img src="%s" class="%s" alt="%s" />', $this->url('images/' . $src), $css, $this->t($alt));
	}
	
	function renderMenuLink($href, $title) {
		if ($this->raw_path == $href) {
			$css = 'active';
		} else {
			$css = '';
		}
		echo sprintf('<li class="%s"><a href="%s" >%s</a></li>', $css, $this->url($href), $this->t($title));
	}
	
	/*
		CONTROLLERS
	*/
	
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
	
}