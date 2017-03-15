<?php
	
	if (isPost()) {
		if ($this->z->auth->login(get('user_name'), get('password'))) {
			$this->redirect('admin');
		} else {
			$this->message('Login unsuccessful.', 'error');
		}
	}