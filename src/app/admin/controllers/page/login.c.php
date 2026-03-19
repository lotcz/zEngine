<?php
	$this->setPageTitle('Administration');

	if (z::isPost()) {
		if ($this->z->auth->login(z::get('user_name'), z::get('password'))) {
			if ($this->z->admin->hasAnyRole()) {
				$this->redirectBack('admin');
			} else {
				$this->z->core->redirect('', 302);
			}
		} else {
			$this->message('Login unsuccessful!', 'error');
		}
	}
