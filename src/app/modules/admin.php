<?php

require_once __DIR__ . '/../models/admin.m.php';

/**
* Module that handles administration area.
*/
class adminModule extends zModule {

	public $depends_on = ['auth', 'menu'];
	public $also_install = ['forms', 'tables'];

	// url part defining admin protected area
	public $base_url = 'admin';

	// directory base for views and controllers of admin protected area, relative to application dir
	public $base_dir = 'admin/';

	public $is_admin_area = false;

	// page of admin area that is accessible for public
	public $public_pages = ['login', 'forgotten-password', 'reset-password'];

	public $is_public_page = false;

	// when authentication fails, user is redirected here
	public $login_url = 'login';

	public $is_login_page = false;

	public $menu = null;

	private $authentication_checked = false;

	public $admin = null;

	/**
	* Return true if an admin is authenticated.
	*/
	public function isAuth() {
		$this->checkAuthentication();
		return isset($this->admin);
	}

	/**
	* Verifies if there is an admin logged in.
	* Call this only once in the beginning of request processing and then call to isAuth() method to check whether admin is authenticated.
	*/
	private function checkAuthentication() {
		if (!$this->authentication_checked) {
			$this->admin = null;
			if ($this->z->auth->isAuth()) {
				$admin = new AdminModel($this->z->db);
				$admin->loadByUserId($this->z->auth->user->ival('user_id'));
				if ($admin->is_loaded) {
					$this->admin = $admin;
				}
			}
			$this->authentication_checked = true;
		}
	}

	/**
	* Log user in if login and password are correct and return true if successful.
	* @return bool
	*/
	public function login($login_or_email, $password) : bool {
		if ($this->z->auth->login($login_or_email, $password)) {
			$this->authentication_checked = false;
			return $this->isAuth();
		} else {
			return false;
		}
	}

	public function onEnabled() {
		$this->base_url = $this->getConfigValue('admin_area_base_url', $this->base_url);
		$this->base_dir = $this->getConfigValue('admin_area_base_dir', $this->base_dir);
		$this->login_url = $this->getConfigValue('login_page_url', $this->login_url);
	}

	public function OnBeforeInit() {
		$this->is_admin_area = (count($this->z->core->path) > 0 && ($this->z->core->path[0] == $this->base_url));
		if ($this->is_admin_area) {
			array_shift($this->z->core->path);
			$this->z->core->app_dir .= $this->base_dir;
			$this->z->core->default_app_dir .= $this->base_dir;
			$this->requireModule('forms');
			$this->requireModule('tables');
			$this->is_login_page = (count($this->z->core->path) == 1 && ($this->z->core->path[0] == $this->login_url));
			$this->is_public_page = (count($this->z->core->path) == 1 && (in_array($this->z->core->path[0], $this->public_pages)));
			if (!$this->is_public_page && !$this->isAuth()) {
				$this->z->core->path = [$this->login_url];
			} else if ($this->is_login_page && $this->isAuth()) {
				$this->z->core->path = [$this->base_url];
			}
		}
		$this->initializeAdminMenu();
	}

	public function getAdminAreaURL($page) {
		return $this->base_url . '/' . $page;
	}

	/**
	* Initializes basic admin menu including users, languages etc. based on enabled modules
	*/
	private function initializeAdminMenu() {
		$menu = new zMenu($this->getAdminAreaURL(''), $this->z->core->getData('site_title'));

		if ($this->isAuth()) {

			//custom menu from app's admin config
			$menu->loadItemsFromArray($this->getConfigValue('custom_menu'));

			//standard admin menu
			if ($this->getConfigValue('show_default_menu', false)) {
				$submenu = $menu->addSubmenu('Administration');
				$submenu->addItem('admin/users', 'Users');
				$submenu->addItem('admin/administrators', 'Administrators');
				$submenu->addSeparator();
				$submenu->addHeader('Advanced');
					$submenu->addItem('admin/job-runner', 'Jobs');
				if ($this->z->isModuleEnabled('alias')) {
					$submenu->addItem('admin/aliases', 'Aliases');
				}
				$submenu->addItem('admin/languages', 'Languages');
				$submenu->addItem('admin/currencies', 'Currencies');
				$submenu->addItem('admin/ip-failed-attempts', 'Failed Login Attempts');
				$submenu->addItem('admin/info', 'PHP-info');
				$submenu->addItem('admin/about', 'About');
			}
			$user = $this->z->auth->user;
			$usermenu = $menu->addRightSubmenu($user->getLabel());
			$usermenu->addItem('admin/default/default/user/edit/' . $user->val('user_id'), 'User Profile');
			$usermenu->addItem('admin/change-password', 'Change Password');
			$usermenu->addItem('admin/logout', 'Log Out');
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
	public function renderAdminTable($entity_name, $fields, $filter_fields = null, $view_name = null) {
		if (!isset($view_name)) {
			$view_name = $entity_name;
		}
		$form = new zForm($entity_name, '', 'POST', 'form-inline');
		$form->type = 'inline';
		$form->render_wrapper = true;
		$form->addField([
			'name' => 'form_buttons',
			'type' => 'buttons',
			'buttons' => [
				['type' => 'link', 'label' => '+ Add', 'css' => 'btn btn-success mr-2' , 'link_url' => $this->base_url . '/' . $form->detail_page . '?r=' . $this->z->core->raw_path]
			]
		]);
		if (isset($filter_fields)) {
			$form->add($filter_fields);
			$form->addField([
				'name' => 'form_filter_button',
				'type' => 'buttons',
				'buttons' => [
					['type' => 'submit', 'label' => 'Search', 'css' => 'btn btn-success mr-2'],
					['type' => 'link', 'label' => 'Reset', 'css' => 'btn btn-default mr-2', 'link_url' => $this->z->core->raw_path]
				]
			]);
		}
		if (z::isPost()) {
			$form->processInput($_POST);
		}
		$this->z->core->setData('form', $form);

		$table = $this->z->tables->createTable($entity_name, $view_name, 'table-striped table-sm table-bordered table-hover mt-2');
		$table->id_field_name = $entity_name . '_id';
		$table->edit_link = sprintf('admin/default/default/%s/edit/', $form->detail_page) . '%d';
	 	$table->new_link = sprintf('admin/default/default/%s', $form->detail_page);

		$table->add($fields);
		if (isset($filter_fields)) {
			$table->filter_form = $form;
		}
		$this->z->tables->prepareTable($table);
		$this->z->core->setData('table', $table);
		$this->z->core->setPageTemplate('admin');
	}

	public function getAdminFormButtons($form, $model_class_name) {
		$buttons = [];
		$buttons[] = ['type' => 'link', 'label' => 'Back', 'link_url' => $this->z->core->return_path, 'css' => 'm-2'];

		$model_id = $form->data->ival($model_class_name::getIdName());
		if ($model_id > 0) {
			$delete_question = $this->z->core->t('Are you sure to delete this item?');
			$delete_url = $this->z->core->url(sprintf($this->base_url . '/default/default/' . $form->detail_page . '/delete/%d', $model_id), $this->z->core->return_path);
			$buttons[] = ['type' => 'button', 'label' => 'Delete', 'onclick' => 'deleteItemConfirm(\'' . $delete_question . '\',' . '\'' . $delete_url . '\');', 'css' => 'btn btn-danger m-2' ];
		}

		$buttons[] = ['type' => 'submit', 'label' => 'Save', 'onclick' => 'validateForm_' . $form->id . '(event);', 'css' => 'btn btn-success m-2' ];
		return $buttons;
	}

	/**
	* Render default form for administration area.
	*/
	public function renderAdminForm($model_class_name, $fields, $onBeforeUpdate = null, $onAfterUpdate = null, $onBeforeDelete = null, $onAfterDelete = null) {
		$entity_name = $model_class_name::getTableName();
		$form = new zForm($entity_name);
		$form->type = 'vertical';
		$form->entity_title = ucwords(str_replace('_', ' ', $entity_name));
		$form->render_wrapper = true;
		$form->onBeforeUpdate = $onBeforeUpdate;
		$form->onAfterUpdate = $onAfterUpdate;
		$form->onBeforeDelete = $onBeforeDelete;
		$form->onAfterDelete = $onAfterDelete;

		$form->addField(
			[
				'name' => $model_class_name::getIdName(),
				'type' => 'hidden'
			]
		);
		$form->add($fields);
		$this->z->forms->processForm($form, $model_class_name);

		if ($this->z->forms->pathAction() == 'edit') {
			$this->z->core->setPageTitle($this->z->core->t($form->entity_title) . ': ' . $this->z->core->t('Edit'));
		} else {
			$this->z->core->setPageTitle($this->z->core->t($form->entity_title) . ': ' . $this->z->core->t('Add'));
		}

		$form->addField(
			[
				'name' => 'form_buttons',
				'type' => 'buttons',
				'buttons' => $this->getAdminFormButtons($form, $model_class_name)
			]
		);

		$this->z->core->setData('form', $form);
		$this->z->core->setPageTemplate('admin');
	}

	/**
	* Create and activate admin account. Used for db initialization.
	*/
	public function createActiveAdminAccount($full_name, $login, $email, $password) {
		$user = $this->z->auth->createActiveUser($full_name, $login, $email, $password);
		$admin = new AdminModel($this->z->db);
		$admin->set('admin_user_id', $user->ival('user_id'));
		$admin->save();
	}

}
