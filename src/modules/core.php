<?php

class coreModule extends zModule {
	
	//path to application directory
	public $app_dir = null;
	
	//path to default zEngine application directory
	public $default_app_dir = __DIR__ . '/../app/';
	
	public $base_url = '';
	public $debug_mode = false;
	public $error_page = 'error.html';
	
	public $return_path = false;
	
	public $data = [
		'page_title' => 'Page Title',
		'site_title' => null
	];
	
	public $include_js = [];
	public $include_css = [];
	
	public $controllers = ['master' => 'default', 'main' => 'default', 'page' => 'default'];	
	public $templates = ['master' => null, 'main' => null, 'page' => null];
	
	public $path = [];
	public $raw_path = '';
	
	public function onEnabled() {
		$this->requireModule('errorlog');
		$this->requireConfig('base_url');
		$this->app_dir = $this->z->app_dir;
		$this->base_url = $this->config['base_url'];
		$this->debug_mode = $this->config['debug_mode'];
		$this->error_page = $this->config['error_page'];
		$this->setData('site_title', $this->getConfigValue('site_title', 'Site Name'));
		$this->return_path = get('r', false);
	}
	
	public function pathExists($index) {
		return isset($this->path[$index]);
	}
	
	public function getPath($index = null) {
		if ($index === null) {
			return $this->path;
		} elseif ($index >= 0) {
			if ($this->pathExists($index)) {
				return $this->path[$index];
			} else {
				return null;
			}
		} else {
			$ind = count($this->path) + $index;
			if ($this->pathExists($ind)) {
				return $this->path[$ind];
			} else {
				return null;
			}
		}
	}
	
	public function parseURL($url_path) {
		$this->path = explode('/', trimSlashes(strtolower($url_path)));
		$this->raw_path = implode('/', $this->path);
	}
	
	public function chooseControllers() {
		$path_items = count($this->path);
		if ($path_items > 0) {
			if ($path_items == 1) {
				$this->setPageController($this->path[0]);
			} elseif ($path_items == 2) {
				$this->setMainController($this->path[0]);
				$this->setPageController($this->path[1]);
			} else {
				$this->setMasterController($this->path[0]);
				$this->setMainController($this->path[1]);
				$this->setPageController($this->path[2]);
			}
		}
	}
	
	public function setData($name, $value) {
		$this->data[$name] = $value;
	}
	
	public function getData($name) {
		return $this->data[$name];
	}
	
	public function dataExists($name) {
		return isset($this->data[$name]);
	}
	
	public function setPageTitle($page_title) {
		$this->setData('page_title', $this->t($page_title));
	}
	
	public function includeJS($js_path, $abs = false) {
		if ($abs) {
			$this->include_js[] = $js_path;			
		} else {
			$this->include_js[] = $this->url($js_path);
		}
	}
	
	public function includeCSS($css_path, $abs = false) {
		if ($abs) {
			$this->include_css[] = $css_path;			
		} else {
			$this->include_css[] = $this->url($css_path);
		}
	}
	
	/*
		HELPERS
	*/
	
	public function requireClass($class_name) {
		require_once __DIR__ . "/../classes/$class_name.php";
	}	
	
	public function redirect($url, $statusCode = 303) {
		header('Location: ' . trimSlashes($this->base_url) . '/' . trimSlashes($url), true, $statusCode);
		die();
	}	
	
	public function url($link = '', $ret = null) {
		$url = $this->base_url . '/' . $link;
		if (isset($ret)) {
			$url .= '?r=' . $ret;
		}		
		return $url;
	}
	
	public function getLink($path, $title, $css = '', $ret = null) {
		return sprintf('<a href="%s" class="%s">%s</a>', $this->url($path, $ret), $css, $this->t($title));
	}
	
	public function t($s) {
		if ($this->z->moduleEnabled('i18n')) {
			$t = $this->z->i18n->translate($s);			
		} else {
			$t = $s;
		}
		if (func_num_args() > 1) {
			$args = func_get_args();
			array_shift($args);
			array_unshift($args, $t);
			return call_user_func_array('sprintf', $args);
		} else {
			return $t;
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
		ADMIN HELPERS 
	*/
	
	public function renderAdminMenu() {
		$this->z->admin->renderAdminMenu();
	}
	
	public function renderAdminTable($table_name, $entity_name, $fields) {
		$this->z->admin->renderAdminTable($table_name, $entity_name, $fields);
	}
	
	public function renderAdminForm($entity_name, $model_class_name, $fields) {
		$this->z->admin->renderAdminForm($entity_name, $model_class_name, $fields);
	}
			
	/*
		RENDERING
	*/	
	
	public function setTemplate($type, $template_name) {
		$this->templates[$type] = $template_name;
	}
	
	public function setPageTemplate($template_name) {
		$this->setTemplate('page', $template_name);
	}
	
	public function setMainTemplate($template_name) {
		$this->setTemplate('main', $template_name);
	}
	
	public function setMasterTemplate($template_name) {
		$this->setTemplate('master', $template_name);
	}
	
	public function renderView($type = 'page') {
		if (!isset($this->templates[$type])) {
			 $this->templates[$type] = $this->controllers[$type];
		}
		$template_path = $this->app_dir . "views/$type/" .  $this->templates[$type] . '.v.php';
		if (file_exists($template_path)) {
			include $template_path;
		} else {
			$default_template_path = $this->default_app_dir . "views/$type/" .  $this->templates[$type] . '.v.php';
			if (file_exists($default_template_path)) {
				include $default_template_path;
			} else {
				throw new Exception("Template for $type view not found: $template_path!");
			}
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
		
	public function renderJSIncludes() {
		foreach ($this->include_js as $js) {
			echo sprintf('<script src="%s"></script>',$js);
		}
	}
	
	public function renderCSSIncludes() {
		foreach ($this->include_css as $css) {
			echo sprintf('<link rel="stylesheet" href="%s">',$css);
		}
	}
	
	/*
		CONTROLLERS
	*/
	
	public function setController($type, $controller_name) {
		$this->controllers[$type] = $controller_name;
	}
	
	public function setPageController($controller_name) {
		$this->setController('page', $controller_name);
	}
	
	public function setMainController($controller_name) {
		$this->setController('main', $controller_name);
	}
	
	public function setMasterController($controller_name) {
		$this->setController('master', $controller_name);
	}
	
	public function runController($type = 'page') {
		if (!isset($this->controllers[$type])) {
			 $this->controllers[$type] = $this->$type;
		}
		$controller_path = $this->app_dir . "controllers/$type/" . $this->controllers[$type] . '.c.php';
		if (file_exists($controller_path)) {
			include $controller_path;
		} else {
			$default_controller_path = $this->default_app_dir . "controllers/$type/" . $this->controllers[$type] . '.c.php';
			if (file_exists($default_controller_path)) {
				include $default_controller_path;
			}
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