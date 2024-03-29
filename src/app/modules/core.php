<?php

/**
* This is the beating heart of zEngine.
*  Core is the only module that is required to run zEngine application.
*  It handles basic page processing and rendering according to MVC principles.
*/
class coreModule extends zModule {

	public $depends_on = ['errorlog'];
	public $also_install = [];

	public $app_version = 1.0;
	public $minimum_z_version = 10.5;

	//path to application directory
	public $app_dir = null;

	//path to default zEngine application directory
	public $default_app_dir = '';

	public $default_encoding = 'UTF-8';

	public $base_url = '';
	public $debug_mode = false;
	public $error_page = 'error.html';
	public $error_view = 'error';
	public $not_found_page = 'notfound';

	public $return_path = false;

	public $data = [
		'page_title' => null,
		'site_title' => null
	];

	public $includes = ['head' => [], 'top' => [], 'default' => [], 'bottom' => [], 'admin.head' => [], 'admin.top' => [], 'admin.default' => [], 'admin.bottom' => []];

	// controller names
	public $controllers = ['master' => 'default', 'main' => 'default', 'page' => 'default'];
	// view names
	public $views = ['master' => null, 'main' => null, 'page' => null];
	// view template paths
	public $view_templates = ['master' => null, 'main' => null, 'page' => null];

	public $require_main_view = true;
	public $require_page_view = true;

	public $raw_url = '';
	public $raw_path = '';
	public $path = [];

	private $page_keywords = '';
	private $og_image = null;

	public function onEnabled() {
		$this->default_app_dir = __DIR__ . '/../';
		$this->app_dir = $this->z->app_dir;
		$this->base_url = $this->getConfigValue('base_url');
		$this->debug_mode = $this->getConfigValue('debug_mode', $this->debug_mode);
		$this->error_page = $this->getConfigValue('error_page', $this->error_page);
		$this->not_found_page = $this->getConfigValue('not_found_page', $this->not_found_page);

		$this->app_version = $this->getConfigValue('app_version', $this->app_version);
		$this->minimum_z_version = $this->getConfigValue('minimum_z_version', $this->minimum_z_version);

		$require_z_major_version = intval(floor($this->minimum_z_version));
		$actual_z_major_version = intval(floor($this->z->version));
		if ($actual_z_major_version != $require_z_major_version) {
			throw new Exception(sprintf('Application is for zEngine version %d! Actual zEngine version is %s.', $require_z_major_version, $this->z->version));
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

		$this->includeJS('resources/z.js', 'head');
		$this->includeJS('resources/z.js', 'admin.head');

		// process default includes
		$includes = $this->getConfigValue('includes',[]);
		foreach ($includes as $include) {
			$this->z->core->addToIncludes($include[0], $include[1], $include[2]);
		}

		if (isset($_GET['path'])) {
			$this->raw_url = z::trimSlashes(strtolower($_GET['path']));
			$this->parseURL($this->raw_url);
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
	 * parseUrl() must be called before this or path property must be set in some other way.
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
		$page_title = $this->getData('page_title', '');
		$site_title = $this->getData('site_title', '');
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
		if (isset($fallback_url)) {
			$this->redirect($fallback_url);
		} elseif ($this->return_path) {
			$this->redirect($this->return_path);
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

	public function urlWithRet($link, $ret = null){
		if ($ret == null) {
			$ret = $this->raw_path;
		}
		return $this->url($link, $ret);
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
	   return z::xssafe($data);
	}

	/*
		ADMIN HELPERS
	*/

	public function renderAdminMenu() {
		$this->z->admin->renderAdminMenu();
	}

	public function renderAdminTable($entity_name, $fields, $view_name = null, $sort_fields = [], $default_sort = null, $filter_fields = null) {
		$this->z->admin->renderAdminTable($entity_name, $fields, $view_name, $sort_fields, $default_sort, $filter_fields);
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

	public function includePartial($name, $placement = 'bottom') {
		$this->addToIncludes($name, 'partial_view', $placement);
	}

	public function insertJS($js_content, $placement = 'head') {
		$this->addToIncludes($js_content, 'inline_js', $placement);
	}

	public function includeJS($js_path, $placement = 'bottom') {
		$this->addToIncludes($js_path, 'link_js', $placement);
	}

	public function includeFavicon($path = 'favicon.ico') {
		$this->addToIncludes($path, 'favicon', 'head');
	}

	public function includeCSS($css_path, $placement = 'head') {
		$this->addToIncludes($css_path, 'link_css', $placement);
	}

	public function includePrintCSS($css_path) {
		$this->addToIncludes($css_path, 'print_css', 'head');
	}

	public function includeLESS($less_path, $placement = 'head') {
		$this->addToIncludes($less_path, 'link_less', $placement);
	}

	public function processLinkUrl($url) {
		$abs = z::startsWith($url, 'http://') || z::startsWith($url, 'https://');
		if (!$abs) {
			$url = $this->url($url);
			$url .= sprintf('?v=%s', $this->app_version);
		}
		return $url;
	}

	public function renderIncludes($placement = 'default') {
		foreach ($this->includes[$placement] as $incl) {
			switch ($incl[1]) {
				case 'partial_view':
					$this->renderPartialView($incl[0]);
				break;
				case 'inline_js':
					if (is_object($incl[0]) || is_array($incl[0])) {
						echo '<script>';
						foreach ($incl[0] as $key => $value) {
							echo sprintf('const %s = %s;', $key, z::formatForJS($value));
						}
						echo '</script>' . z::$crlf;
					} else {
						echo sprintf('<script>%s</script>' . z::$crlf, $incl[0]);
					}
				break;
				case 'link_js':
					echo sprintf('<script src="%s"></script>' . z::$crlf, $this->processLinkUrl($incl[0]));
				break;
				case 'link_js_module':
					echo sprintf('<script type="module" src="%s"></script>' . z::$crlf, $this->processLinkUrl($incl[0]));
				break;
				case 'link_css':
					echo sprintf('<link rel="stylesheet" type="text/css" href="%s">' . z::$crlf, $this->processLinkUrl($incl[0]));
				break;
				case 'print_css':
					echo sprintf('<link rel="stylesheet" type="text/css" href="%s" media="print">' . z::$crlf, $this->processLinkUrl($incl[0]));
				break;
				case 'link_less':
					echo sprintf('<link rel="stylesheet/less" type="text/css" href="%s" />' . z::$crlf, $this->processLinkUrl($incl[0]));
				break;
				case 'favicon':
					echo sprintf('<link rel="shortcut icon" type="image/x-icon" href="%s" />' . z::$crlf, $this->processLinkUrl($incl[0]));
				break;
				default:
					throw new Exception(sprintf('Unknown include type: %s', $incl[1]));
				break;
			}
		}
	}

	public function showErrorView($message = null) {
		$this->setPageView($this->error_view);
		$this->setPageTitle('Error');
		if (isset($message)) {
			$this->message($message, 'error');
		}
	}

	public function showNotFoundView() {
		$not_found_template = $this->findViewTemplatePath('page', $this->not_found_page);
		if (!empty($not_found_template)) {
			$this->controllers['page'] = $this->not_found_page;
			$this->runController('page');
			include $not_found_template;
		} else {
			echo "404 - Not found!";
		}
	}

	/*
		RENDERING
	*/

	public function setTemplate($type, $template_name) {
		$this->view_templates[$type] = $template_name;
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

	public function setView($type, $view_name) {
		$this->views[$type] = $view_name;
		$this->setTemplate($type, null);
	}

	public function setPageView($view_name) {
		$this->setView('page', $view_name);
	}

	public function setMainView($view_name) {
		$this->setView('main', $view_name);
	}

	public function setMasterView($view_name) {
		$this->setView('master', $view_name);
	}

	public function getViewName($type) {
		$name = $this->views[$type];
		if (empty($name)) {
			$name = $this->controllers[$type];
		}
		return $name;
	}

	/**
	 * Find a view template path of given type. View file must exist or null returned.
	 * @param $type
	 * @param $name
	 * @return string|null full path to view file or null if not found
	 */
	public function findViewTemplatePath($type, $name) {
		$template_path = $this->app_dir . "views/$type/" . $name . '.v.php';
		if (file_exists($template_path)) {
			return $template_path;
		} else {
			$default_template_path = $this->default_app_dir . "views/$type/" . $name . '.v.php';
			if (file_exists($default_template_path)) {
				return $default_template_path;
			}
		}
		return null;
	}

	/**
	 * Find a view of given type. View file must exist or null returned.
	 * @param $type
	 * @return string|null full path to view file or null if not found
	 */
	public function findViewTemplate($type) {
		if (empty($this->view_templates[$type])) {
			$name = $this->getViewName($type);
			$this->setTemplate($type, $this->findViewTemplatePath($type, $name));
		}
		return $this->view_templates[$type];
	}

	public function renderView($type = 'page') {
		$selected_template_path = $this->findViewTemplate($type);

		if (!empty($selected_template_path)) {
			// make data available to the view
			foreach ($this->data as $data_key => $data_value) {
				if (!isset($$data_key)) {
					$$data_key = $data_value;
				}
			}
			include $selected_template_path;
		} else {
			if ($this->debug_mode) {
				$view_name = $this->views[$type];
				echo "Template <strong>$type</strong> view not found for <strong>$view_name</strong>!";
			} else {
				$this->showNotFoundView();
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
		$template_path = $this->findViewTemplatePath('partial', $partial_name);
		if (!empty($template_path)) {
			if (!empty($data)) {
				foreach ($data as $data_key => $data_value) {
					if (!isset($$data_key)) {
						$$data_key = $data_value;
					}
				}
			}
			include $template_path;
		} else {
			echo "Template for partial view <strong>$partial_name</strong> not found!";
		}
	}

	public function renderMessages() {
		$this->z->messages->render();
	}

	public function renderLink($href, $title, $css = '', $ret = null) {
		echo $this->getLink($href, $title, $css, $ret);
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

	public function getOgImage() {
		return $this->og_image;
	}

	public function setOgImage($image) {
		$this->og_image = $image;
	}

}
