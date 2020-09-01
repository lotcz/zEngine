<?php

/**
* This is the beating heart of zEngine.
*  Core is the only module that is required to run zEngine application.
*  It handles basic page processing and rendering according to MVC principles.
*/
class coreModule extends zModule {

	public $depends_on = ['errorlog'];
	//public $also_install = ['i18n'];

	public $app_version = 0.0;
  public $require_z_version = 4;
	public $minimum_z_version = 4;

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

	private $page_keywords = '';

	public function onEnabled() {
		$this->default_app_dir = __DIR__ . '/../';
		$this->app_dir = $this->z->app_dir;
		$this->base_url = $this->getConfigValue('base_url');
		$this->debug_mode = $this->getConfigValue('debug_mode', $this->debug_mode);
		$this->error_page = $this->getConfigValue('error_page', $this->error_page);
		$this->not_found_path = $this->getConfigValue('not_found_path', $this->not_found_path);

		$this->app_version = $this->getConfigValue('app_version', $this->app_version);
		$this->require_z_version = intval($this->getConfigValue('require_z_version', $this->require_z_version));
		$this->minimum_z_version = $this->getConfigValue('minimum_z_version', $this->minimum_z_version);

		if (intval($this->z->version) != $this->require_z_version) {
			throw new Exception(sprintf('Application is for zEngine version %d. zEngine is version %s.', $this->require_z_version, $this->z->version));
		}

		if ($this->z->version < $this->minimum_z_version) {
			throw new Exception(sprintf('zEngine version %s is too old. Application requires at least version %s.', $this->z->version, $this->minimum_z_version));
		}

		$this->setData('site_title', $this->getConfigValue('site_title', 'Site Name'));
		$this->return_path = z::get($this->getConfigValue('return_path_name'), false);

		// activate default modules
		$default_modules = $this->getConfigValue('default_modules', []);
		foreach ($default_modules as $module_name) {
			$this->requireModule($module_name);
		}

		// process default includes
		$includes = $this->getConfigValue('includes',[]);
		foreach ($includes as $include) {
			$this->z->core->addToIncludes(($include[1]) ? $include[0] : $this->z->core->url($include[0]), $include[2], $include[3]);
		}

	}

	public function installAllModules($db_login = null, $db_password = null, $db_name = null) {
		$installed_modules = [];
		foreach ($this->getConfigValue('default_modules', []) as $module_name) {
			$this->installModule($module_name, $installed_modules, $db_login, $db_password, $db_name);
		}
		foreach ($this->getConfigValue('also_install_modules', []) as $module_name) {
			$this->installModule($module_name, $installed_modules, $db_login, $db_password, $db_name);
		}
	}

	private function installModule($module_name, &$installed_modules, $db_login = null, $db_password = null, $db_name = null) {
		if (!isset($installed_modules[$module_name])) {
			$this->requireModule($module_name);
			$module = $this->z->$module_name;
			foreach ($module->depends_on as $depend_module_name) {
				$this->installModule($depend_module_name, $installed_modules, $db_login, $db_password, $db_name);
			}
			foreach ($module->also_install as $also_module_name) {
				$this->installModule($also_module_name, $installed_modules, $db_login, $db_password, $db_name);
			}
			$module->install($db_login, $db_password, $db_name);
			$installed_modules[$module_name] = true;
		}
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
	public function getData($name, $default = null) {
		if ($this->dataExists($name) && ($this->data[$name] !== null)) {
			return $this->data[$name];
		} else {
			return $default;
		}
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

	public function getPageTitle() {
		return $this->getData('page_title');
	}

	public function getFullPageTitle() {
		$page_title = $this->getData('page_title');
		$site_title = $this->getData('site_title');
		if ((strlen($page_title) > 0) && ($page_title != $site_title)) {
			return $page_title . ' - ' .  $site_title;
		} else {
			return $site_title;
		}
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
		$translated_title = $this->t($title);
		return sprintf('<a href="%s" title="%s" class="%s">%s</a>', $this->url($path, $ret), $translated_title, $css, $translated_title);
	}

	/**
	* Translate string into current application language.
	*/
	public function t($s) {
		if ($this->z->isModuleEnabled('i18n')) {
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
	* send new message into messages queue.
	*/
	public function message($text, $type = 'info') {
		$this->z->messages->add($this->t($text), $type);
	}

	/**
	* format money according to selected language and currency.
	*/
	public function formatMoney($price) {
		if ($this->z->isModuleEnabled('i18n')) {
			return $this->z->i18n->formatMoney($price);
		} else {
			return $price;
		}
	}

	/**
	* convert money into selected currency (from application's default currency with unit value of 1).
	*/
	public function convertMoney($price) {
		if ($this->z->isModuleEnabled('i18n')) {
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
		if ($this->z->isModuleEnabled('i18n')) {
			return $this->z->i18n->selected_language->formatDecimal($number, $decimals);
		} else {
			return $number;
		}
	}

	public function formatInteger($number) {
		if ($this->z->isModuleEnabled('i18n')) {
			return $this->z->i18n->selected_language->formatInteger($number);
		} else {
			return $number;
		}
	}

	public function formatDate($date) {
		if ($this->z->isModuleEnabled('i18n')) {
			return $this->z->i18n->selected_language->formatDate($date);
		} else {
			return $date;
		}
	}

	public function formatDatetime($date) {
		if ($this->z->isModuleEnabled('i18n')) {
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

	public function renderAdminTable($entity_name, $fields, $filter_fields = null, $view_name = null) {
		$this->z->admin->renderAdminTable($entity_name, $fields, $filter_fields, $view_name);
	}

	public function renderAdminForm($model_class_name, $fields, $onBeforeUpdate = null, $onAfterUpdate = null, $onBeforeDelete = null, $onAfterDelete = null) {
		$this->z->admin->renderAdminForm($model_class_name, $fields, $onBeforeUpdate, $onAfterUpdate, $onBeforeDelete, $onAfterDelete);
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

	public function includeFavicon($path = 'favicon.ico') {
		$path = $this->url($path);
		$this->addToIncludes($path, 'favicon', 'head');
	}

	public function includeCSS($css_path, $abs = false, $placement = 'head') {
		if (!$abs) {
			$css_path = $this->url($css_path);
		}
		$this->addToIncludes($css_path, 'link_css', $placement);
	}

	public function includePrintCSS($css_path, $abs = false) {
		if (!$abs) {
			$css_path = $this->url($css_path);
		}
		$this->addToIncludes($css_path, 'print_css', 'head');
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
						echo '</script>' . z::$crlf;
					} else {
						echo sprintf('<script>%s</script>' . z::$crlf, $incl[0]);
					}
				break;
				case 'link_js':
					echo sprintf('<script src="%s?v=%s"></script>' . z::$crlf, $incl[0], $this->app_version);
				break;
				case 'link_css':
					echo sprintf('<link rel="stylesheet" type="text/css" href="%s?v=%s">' . z::$crlf, $incl[0], $this->app_version);
				break;
				case 'print_css':
					echo sprintf('<link rel="stylesheet" type="text/css" href="%s?v=%s" media="print">' . z::$crlf, $incl[0], $this->app_version);
				break;
				case 'link_less':
					echo sprintf('<link rel="stylesheet/less" type="text/css" href="%s?v=%s" />' . z::$crlf, $incl[0], $this->app_version);
				break;
				case 'favicon':
					echo sprintf('<link rel="shortcut icon" type="image/x-icon" href="%s?v=%s" />' . z::$crlf, $incl[0], $this->app_version);
				break;
				default:
					throw new Exception(sprintf('Unknown include type: %s', $incl[1]));
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

	public function setView($type, $template_name) {
		$this->setTemplate($type, $template_name);
	}

	public function setPageTemplate($template_name) {
		$this->setTemplate('page', $template_name);
	}

	public function setPageView($template_name) {
		$this->setPageTemplate($template_name);
	}

	public function setMainTemplate($template_name) {
		$this->setTemplate('main', $template_name);
	}

	public function setMainView($template_name) {
		$this->setMainTemplate($template_name);
	}

	public function setMasterTemplate($template_name) {
		$this->setTemplate('master', $template_name);
	}

	public function setMasterView($template_name) {
		$this->setMasterTemplate($template_name);
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

	/**
	*	This will return e-mail address to be rendered into the page in such a way that it can't be scraped by bots.
	* @return String
	*/
	public function secureEmail($email) {
		$email_arr = explode('@', $email);
		$secure_email = '<script>';
		$secure_email .= sprintf('document.write(\'%s\');', z::toHtmlEntities($email_arr[0]));
		$secure_email .= sprintf('document.write(\'%s\');', z::toHtmlEntities('@'));
		$secure_email .= sprintf('document.write(\'%s\');', z::toHtmlEntities($email_arr[1]));
		$secure_email .= '</script>';
		return $secure_email;
	}

	/**
	*	This will render mailto link into the page in such a way that address can't be scraped by bots.
	*/
	public function renderSecureEmailLink($email) {
		$random_token = z::generateRandomToken(4);
		$email_arr = explode('@', $email);
			?><a id="<?=$random_token ?>"><?=$this->t('Turn on Javascript to see the e-email address.') ?></a><script>
					var addr = ['<?=$email_arr[0] ?>', '<?=$email_arr[1] ?>'].join('@');
					var el = document.getElementById('<?=$random_token ?>');
					el.href = 'mailto:' + addr;
					el.textContent = addr;
					el.title = addr;
				</script><?php
	}

	/* META */

	public function getPageKeywords() {
		return $this->page_keywords;
	}

	public function setPageKeywords($keywords) {
		$this->page_keywords = $keywords;
	}

}
