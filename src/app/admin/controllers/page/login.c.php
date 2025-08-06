<?php
	$this->setPageTitle('Administration');

	if (z::isPost()) {
		if ($this->z->auth->login(z::get('user_name'), z::get('password'))) {
			$this->redirectBack('admin');
		} else {
			$this->message('Login unsuccessful!', 'error');
		}
	}
