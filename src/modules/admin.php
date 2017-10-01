<?php

class adminModule extends zModule {

	private $db = null;

	// url part defining admin protected area
	public $base_url = 'admin';

	// directory base for views and controllers of admin protected area, relative to application dir
	public $base_dir = 'admin/';

	public $is_admin_area = false;

	// the only page of admin area that is accessible for public
	// also when authentication fails, user is redirected here
	public $login_url = 'login';

	public $is_login_page = false;

	public $menu = null;

	public function onEnabled() {
		$this->requireModule('auth');
		$this->requireModule('resources');
		$this->requireModule('menu');
		$this->db = $this->z->core->db;
		$this->base_url = $this->getConfigValue('admin_area_base_url', $this->base_url);
		$this->base_dir = $this->getConfigValue('admin_area_base_dir', $this->base_dir);
		$this->login_url = $this->getConfigValue('login_page_url', $this->login_url);
	}

	public function onInit() {
		$this->is_admin_area = (count($this->z->core->path) > 0 && ($this->z->core->path[0] == $this->base_url));
		if ($this->is_admin_area) {
			array_shift($this->z->core->path);
			$this->z->core->app_dir .= $this->base_dir;
			$this->z->core->default_app_dir .= $this->base_dir;
			$this->requireModule('forms');
			$this->requireModule('tables');
			$this->is_login_page = (count($this->z->core->path) == 1 && ($this->z->core->path[0] == $this->login_url));
			if (!$this->is_login_page && !$this->z->auth->isAuth()) {
				$this->z->core->path = [$this->login_url];
			} else if ($this->is_login_page && $this->z->auth->isAuth()) {
				$this->z->core->path = [$this->base_url];
			}
		}

		if ($this->z->auth->isAuth() || $this->is_admin_area) {
			if ($this->z->isDebugMode()) {
				$this->z->core->includeLESS('resources/less/admin.less');
				$this->z->core->includeJS_head('resources/less/less.min.js');
			} else {
				$this->z->core->includeCSS('resources/admin.css');
			}
		}

		$this->initializeAdminMenu();

	}

	public function getAdminAreaURL($page) {
		return $this->base_url . '/' . $page;
	}

	// returns basic admin menu including users, languages etc. based on enabled modules
	private function initializeAdminMenu() {
		$menu = new zMenu($this->getAdminAreaURL(''), 'Home');

		if ($this->z->auth->isAuth()) {

			//custom menu from app's admin config
			$menu->loadItemsFromArray($this->getConfigValue('menu'));

			//standard admin menu
			$submenu = $menu->addSubmenu('Admin');
			$submenu->addHeader('Administrators');
			$submenu->addItem('admin/users', 'Administrators');
			$submenu->addItem('admin/roles', 'Roles');
			$submenu->addItem('admin/permissions', 'Permissions');
			$submenu->addSeparator();
			$submenu->addHeader('Advanced');
			$submenu->addItem('admin/aliases', 'Aliases');
			$submenu->addItem('admin/languages', 'Languages');
			$submenu->addItem('admin/translations', 'Translations');
			$submenu->addItem('admin/ip_failed_attempts', 'Failed login attempts');
			$submenu->addItem('admin/phpinfo', 'PHP Info');
			$submenu->addItem('admin/about', 'About');			

			$menu->addRightItem('admin/logout', 'Log out');
		} else if (!$this->is_login_page) {
			//$menu->addRightItem($this->getAdminAreaURL($this->login_url), 'Log in');
		}

		$this->menu = $menu;
	}

	public function renderAdminMenu() {
		$this->z->menu->renderMenu($this->menu);
	}

	public function renderAdminTable($table_name, $entity_name, $fields) {
		$table = new zAdminTable($table_name, $entity_name);
		$table->add($fields);
		$table->prepare($this->z->core->db);
		$this->z->core->setData('table', $table);
		$this->z->core->setPageTemplate('admin');

		$form = new zForm($entity_name);
		$form->render_wrapper = true;
		$form->addField([
			'name' => 'form_buttons',
			'type' => 'buttons',
			'buttons' => [
				['type' => 'link', 'label' => 'New', 'css' => 'btn btn-success' , 'link_url' => $this->base_url . '/' . $entity_name . '?r=' . $this->z->core->raw_path]
			]
		]);
		$this->z->core->setData('form', $form);
	}

	public function getAdminFormButtons($form) {
		$buttons = [];
		$buttons[] = ['type' => 'link', 'label' => 'Back', 'link_url' => $form->ret];

		$model_id = $form->data->ival($form->data->id_name);
		if ($model_id > 0) {
			$delete_question = $this->z->core->t('Are you sure to delete this item?');
			$delete_url = $this->z->core->url(sprintf($this->base_url . '/default/default/' . $form->id . '/delete/%d', $model_id), $form->ret);
			$buttons[] = ['type' => 'button', 'label' => 'Delete', 'onclick' => 'deleteItemConfirm(\'' . $delete_question . '\',' . '\'' . $delete_url . '\');', 'css' => 'btn btn-error' ];
		}

		$buttons[] = ['type' => 'button', 'label' => 'Save', 'onclick' => 'validateForm_' . $form->id . '();', 'css' => 'btn btn-success' ];
		return $buttons;
	}

	public function renderAdminForm($entity_name, $model_class_name, $fields) {
		$form = new zForm($entity_name);
		$form->entity_title = ucwords(str_replace('_', ' ', $entity_name));
		$form->render_wrapper = true;
		$form->addField(
			[
				'name' => $entity_name . '_id',
				'type' => 'hidden'
			]
		);
		$form->add($fields);
		$this->z->forms->processForm($form, $model_class_name);

		$form->addField(
			[
				'name' => 'form_buttons',
				'type' => 'buttons',
				'buttons' => $this->getAdminFormButtons($form)
			]
		);

		$this->z->core->setData('form', $form);
		$this->z->core->setPageTemplate('admin');
	}

}
