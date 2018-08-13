<?php
	$this->setPageTitle('Administration');

	if (z::isPost()) {
		if ($this->z->admin->login(z::get('user_name'), z::get('password'))) {
			$this->redirect('admin');
		} else {
			$this->message('Login unsuccessful!', 'error');
		}
	}
