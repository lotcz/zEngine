<?php

/**
* Module that handles administration area.
*/
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
			if ($this->z->isDebugMode()) {
				$this->z->core->includeLESS('resources/less/admin.less');
				$this->z->core->includeJS_head('resources/less/less.min.js');
			} else {
				$this->z->core->includeCSS('resources/admin.min.css');
			}
		}	

		$this->initializeAdminMenu();

	}

	public function getAdminAreaURL($page) {
		return $this->base_url . '/' . $page;
	}

	/**
	* Returns basic admin menu including users, languages etc. based on enabled modules 
	*/
	private function initializeAdminMenu() {
		$menu = new zMenu($this->getAdminAreaURL(''), $this->z->core->getConfigValue('site_title', 'Home'));
		
		if ($this->z->auth->isAuth()) {

			//custom menu from app's admin config
			$menu->loadItemsFromArray($this->getConfigValue('menu'));

			//standard admin menu
			if ($this->getConfigValue('show_default_menu', false)) {
				$submenu = $menu->addSubmenu('Administration');
				$submenu->addItem('admin/static_pages', 'Static pages');
				$submenu->addHeader('Customers');
				$submenu->addItem('admin/customers', 'Customers');	
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
				$submenu->addItem('admin/info', 'Server Info');
				$submenu->addItem('admin/about', 'About');
			}
			
			$menu->addRightItem('admin/logout', 'Log Out');
		} else if (!$this->is_login_page) {
			//$menu->addRightItem($this->getAdminAreaURL($this->login_url), 'Log in');
		}

		$this->menu = $menu;
	}

	public function renderAdminMenu() {
		$this->z->menu->renderMenu($this->menu);
	}

	/**
	* Render default table for administration area.
	*/
	public function renderAdminTable($table_name, $entity_name, $fields, $filter_fields = null) {
		$form = new zForm($entity_name, '', 'POST', 'form-inline');
		$form->type = 'inline';
		$form->render_wrapper = true;
		$form->addField([
			'name' => 'form_buttons',
			'type' => 'buttons',
			'buttons' => [
				['type' => 'link', 'label' => '+ Add', 'css' => 'btn btn-success' , 'link_url' => $this->base_url . '/' . $entity_name . '?r=' . $this->z->core->raw_path]
			]
		]);
		if (isset($filter_fields)) {
			$form->add($filter_fields);
			$form->addField([
				'name' => 'form_filter_button',
				'type' => 'buttons',
				'buttons' => [
					['type' => 'submit', 'label' => 'Search', 'css' => 'btn btn-success'],
					['type' => 'link', 'label' => 'Reset', 'css' => 'btn btn-default', 'link_url' => $this->z->core->raw_path]
				]
			]);
		}
		if (z::isPost()) {
			$form->processInput($_POST);
		}
		$this->z->core->setData('form', $form);

		$table = new zAdminTable($table_name, $entity_name);
		$table->add($fields);
		if (isset($filter_fields)) {
			$table->filter_form = $form;
		}
		$table->prepare($this->z->core->db);
		$this->z->core->setData('table', $table);
		$this->z->core->setPageTemplate('admin');
	}

	public function getAdminFormButtons($form) {
		$buttons = [];
		$buttons[] = ['type' => 'link', 'label' => 'Back', 'link_url' => $this->z->core->return_path];

		$model_id = $form->data->ival($form->data->id_name);
		if ($model_id > 0) {
			$delete_question = $this->z->core->t('Are you sure to delete this item?');
			$delete_url = $this->z->core->url(sprintf($this->base_url . '/default/default/' . $form->id . '/delete/%d', $model_id), $this->z->core->return_path);
			$buttons[] = ['type' => 'button', 'label' => 'Delete', 'onclick' => 'deleteItemConfirm(\'' . $delete_question . '\',' . '\'' . $delete_url . '\');', 'css' => 'btn btn-error' ];
		}

		$buttons[] = ['type' => 'submit', 'label' => 'Save', 'onclick' => 'validateForm_' . $form->id . '(event);', 'css' => 'btn btn-success' ];
		return $buttons;
	}

	/**
	* Render default form for administration area.
	*/
	public function renderAdminForm($entity_name, $model_class_name, $fields, $onBeforeUpdate = null, $onAfterUpdate = null, $onBeforeDelete = null, $onAfterDelete = null) {
		$form = new zForm($entity_name);
		$form->entity_title = ucwords(str_replace('_', ' ', $entity_name));
		$form->render_wrapper = true;
		$form->onBeforeUpdate = $onBeforeUpdate;
		$form->onAfterUpdate = $onAfterUpdate;
		$form->onBeforeDelete = $onBeforeDelete;
		$form->onAfterDelete = $onAfterDelete;

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
