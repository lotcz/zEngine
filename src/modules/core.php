<?php

/**
* This is the beating heart of zEngine. 
  Core is the only module that is required to run zEngine application.
  It handles basic page processing and rendering according to MVC principles.
*/
class coreModule extends zModule {

	//path to application directory
	public $app_dir = null;

	//path to default zEngine application directory
	public $default_app_dir = '';

	public $default_encoding = 'UTF-8';
	
	public $base_url = '';
	public $debug_mode = false;
	public $error_page = 'error.html';
	public $error_view = 'error';
	public $not_found_path = 'notfound';
	
	public $return_path = false;

	public $data = [
		'page_title' => null,
		'site_title' => null
	];

	public $includes = ['head' => [], 'default' => [], 'bottom' => []];

	public $controllers = ['master' => 'default', 'main' => 'default', 'page' => 'default'];
	public $templates = ['master' => null, 'main' => null, 'page' => null];

	public $path = [];
	public $raw_path = '';

	public function onEnabled() {
		$this->default_app_dir = __DIR__ . '/../app/';
		$this->requireModule('errorlog');
		$this->requireConfig('base_url');
		$this->app_dir = $this->z->app_dir;
		$this->base_url = $this->getConfigValue('base_url', $this->base_url);
		$this->debug_mode = $this->getConfigValue('debug_mode', $this->debug_mode);
		$this->error_page = $this->getConfigValue('error_page', $this->error_page);
		$this->not_found_path = $this->getConfigValue('not_found_path', $this->not_found_path);
		$this->setData('site_title', $this->getConfigValue('site_title', 'Site Name'));
		$this->return_path = z::get('r', false);		
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
		$this->path = explode('/', z::trimSlashes(strtolower($url_path)));
		$this->raw_path = implode('/', $this->path);
	}

	/**
	* Analyzes $path and choose correct master, main and page controllers.
	*/
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

	/**
	* Send data to view.
	*/	
	public function setData($name, $value) {
		$this->data[$name] = $value;
	}

	/**
	* Retrieve data sent to view.
	*/	
	public function getData($name) {
		return $this->data[$name];
	}

	/**
	* Check if data of given key were sent to view.
	*/	
	public function dataExists($name) {
		return isset($this->data[$name]);
	}

	public function setPageTitle($page_title) {
		$this->setData('page_title', $this->t($page_title));
	}

	public function getFullPageTitle() {
		$page_title = $this->getData('page_title');
		if (strlen($page_title) > 0) {
			return $page_title . ' - ' .  $this->getData('site_title');
		} else {
			return $this->getData('site_title');
		}
	}
	
	public function requireClass($class_name) {
		require_once __DIR__ . "/../classes/$class_name.php";
	}

	public function redirect($url = '', $statusCode = 303) {
		z::redirect(z::trimSlashes($this->base_url) . '/' . z::trimSlashes($url), $statusCode);
	}

	public function redirectBack($fallback_url = null) {
		if ($this->return_path) {
			$this->redirect($this->return_path);
		} elseif (isset($fallback_url)) {
			$this->redirect($fallback_url);
		} else {
			$this->redirect($this->raw_path);
		}
	}

	/**
	* Generate fully qualified URL for a page.
	*/	
	public function url($link = '', $ret = null) {
		$url = $this->base_url . '/' . $link;
		if (isset($ret) && strlen($ret) > 0) {
			$url .= '?r=' . $ret;
		}
		return $url;
	}

	/**
	* Generate link HTML wih fully qualified URL for a page.
	*/
	public function getLink($path, $title, $css = '', $ret = null) {
		if ($this->raw_path == $path) {
			$css .= ' active';
		}
		return sprintf('<a href="%s" class="%s">%s</a>', $this->url($path, $ret), $css, $this->t($title));
	}

	/**
	* Translate string into current application language.
	*/
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

	/**
	* return true if there is administrator user authenticated.
	*/
	public function isAuth() {
		return $this->z->auth->isAuth();
	}

	/**
	* return authenticated administrator user .
	*/
	public function getUser() {
		return $this->z->auth->user;
	}

	/**
	* return true if there is customer user authenticated.
	*/
	public function isCustAuth() {
		return $this->z->custauth->isAuth();
	}

	/**
	* return authenticated customer user .
	*/
	public function getCustomer() {
		return $this->z->custauth->customer;
	}

	/**
	* send new message into messages queue.
	*/
	public function message($text, $type = 'info') {
		$this->z->messages->add($this->t($text), $type);
	}

	/**
	* format money according to selected language and currency.
	*/
	public function formatMoney($price) {
		if ($this->z->moduleEnabled('i18n')) {
			return $this->z->i18n->formatMoney($price);
		} else {
			return $price;
		}
	}

	/**
	* convert money into selected currency (from application's default currency with unit value of 1).
	*/
	public function convertMoney($price) {
		if ($this->z->moduleEnabled('i18n')) {
			return $this->z->i18n->convertMoney($price);
		} else {
			return $price;
		}
	}

	/**
	* both convert and format money according to selected language and currency.
	*/
	public function convertAndFormatMoney($price) {
		return $this->formatMoney($this->convertMoney($price));
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
			return $date;
		}
	}
	
	public function formatDatetime($date) {
		if ($this->z->moduleEnabled('i18n')) {
			return $this->z->i18n->selected_language->formatDatetime($date);
		} else {
			return $date;
		}
	}

	public function xssafe($data) {
	   return z::xssafe($data, $this->default_encoding);
	}
	
	/*
		ADMIN HELPERS
	*/

	public function renderAdminMenu() {
		$this->z->admin->renderAdminMenu();
	}

	public function renderAdminTable($table_name, $entity_name, $fields, $filter_fields = null) {
		$this->z->admin->renderAdminTable($table_name, $entity_name, $fields, $filter_fields);
	}

	public function renderAdminForm($entity_name, $model_class_name, $fields, $onBeforeUpdate = null, $onAfterUpdate = null, $onBeforeDelete = null, $onAfterDelete = null) {
		$this->z->admin->renderAdminForm($entity_name, $model_class_name, $fields, $onBeforeUpdate, $onAfterUpdate, $onBeforeDelete, $onAfterDelete);
	}

	/* 
	
	INCLUDES
	
	included JS, CSS files and other content 
	
	*/
	
	public function addToIncludes($content, $type, $placement = 'default') {
		$this->includes[$placement][] = [$content, $type];
	}	
	
	public function insertJS($js_content, $placement = 'head') {
		$this->addToIncludes($js_content, 'inline_js', $placement);
	}
	
	public function includeJS($js_path, $abs = false, $placement = 'bottom') {
		if (!$abs) {
			$js_path = $this->url($js_path);
		}
		$this->addToIncludes($js_path, 'link_js', $placement);
	}

	public function includeJS_head($js_path, $abs = false) {
		$this->includeJS($js_path, $abs, 'head');
	}

	public function includeCSS($css_path, $abs = false, $placement = 'head') {
		if (!$abs) {			
			$css_path = $this->url($css_path);
		}
		$this->addToIncludes($css_path, 'link_css', $placement);
	}

	public function includeLESS($less_path, $abs = false, $placement = 'head') {
		if (!$abs) {
			$less_path = $this->url($less_path);
		}
		$this->addToIncludes($less_path, 'link_less', $placement);
	}
	
	public function renderIncludes($placement = 'default') {
		foreach ($this->includes[$placement] as $incl) {
			switch ($incl[1]) {
				case 'inline_js':
					if (is_object($incl[0]) || is_array($incl[0])) {
						echo '<script>';
						foreach ($incl[0] as $key => $value) {							
							echo sprintf('var %s = %s;', $key, z::formatForJS($value));														
						}
						echo '</script>';
					} else {
						echo sprintf('<script>%s</script>', $incl[0]);
					}
				break;
				case 'link_js':
					echo sprintf('<script src="%s"></script>', $incl[0]);
				break;
				case 'link_css':
					echo sprintf('<link rel="stylesheet" type="text/css" href="%s">', $incl[0]);
				break;
				case 'link_less':
					echo sprintf('<link rel="stylesheet/less" type="text/css" href="%s" />', $incl[0]);
				break;
				default:
					throw new Exception(sprintf('Unknown include type: ', $incl[1]));
				break;
			}
		}
	}	
	
	public function showErrorView($message = null) {
		$this->setPageTemplate($this->error_view);
		$this->setPageTitle('Error');
		if (isset($message)) {
			$this->message($message, 'error');
		}
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
				if ($this->debug_mode) {
					echo "Template for $type view not found: $default_template_path!";
				} else {
					$this->redirect($this->not_found_path);
				}
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

	public function renderPartialView($partial_name, $data = null) {
		$template_path = $this->app_dir . 'views/partial/' .  $partial_name . '.v.php';
		if (file_exists($template_path)) {
			$this->setData("partials.$partial_name", $data);
			include $template_path;
		} else {
			$default_template_path = $this->default_app_dir . "views/partial/" .  $partial_name . '.v.php';
			if (file_exists($default_template_path)) {
				include $default_template_path;
			} else {
			echo "Template for partial view $partial_name not found: $template_path!";
			}
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
