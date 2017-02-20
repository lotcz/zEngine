<?php

class adminModule extends zModule {
	
	private $db = null;
	
	// url part defining admin protected area
	public $base_url = 'admin';
	
	// directory base for views and controllers of admin protected area
	public $base_dir = 'admin/';
	
	// the only page of admin area that is accessible for public
	// also when authentication fails, user is redirected here
	public $login_url = 'login';
	
	public function onEnabled() {
		$this->requireModule('auth');			
		$this->db = $this->z->core->db;
		$this->base_url = $this->getConfigValue('admin_area_base_url', $this->base_url);
		$this->base_dir = $this->getConfigValue('admin_area_base_dir', $this->base_dir);
		$this->login_url = $this->getConfigValue('login_page_url', $this->login_url);
	}
	
	public function onInit() {
		$is_admin_area = (count($this->z->core->path) > 0 && ($this->z->core->path[0] == $this->base_url));
		if ($is_admin_area) {
			$this->z->core->content_dir = $this->base_dir;
			$this->requireModule('forms');
			$this->requireModule('tables');
			$is_login_page = (count($this->z->core->path) == 2 && ($this->z->core->path[1] == $this->login_url));
			if (!$is_login_page && !$this->z->auth->isAuth()) {
				$this->z->core->parseURL($this->base_url . '/' . $this->login_url);
			} else if ($is_login_page && $this->z->auth->isAuth()) {
				$this->z->core->parseURL($this->base_url);
			} else {
				array_shift($this->z->core->path);
				$this->z->core->raw_path = implode('/', $this->z->core->path);
			}
		}
	}
	
	// renders basic admin menu including users, languages etc. based on enabled modules
	public function renderAdminSubMenu() {
		
	}

			
}