<?php
	$this->includeJS('resources/login.js');

	if (z::isPost()) {
		if ($this->z->auth->login(z::get('user_name'), z::get('password'))) {
			$this->redirect('admin');
		} else {
			$this->message('Login unsuccessful.', 'error');
		}
	}
