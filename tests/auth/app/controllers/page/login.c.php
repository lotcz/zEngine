<?php
	
	if (isPost()) {
		if ($this->z->auth->login(get('user_name'), get('password'))) {
			$this->redirect('');
		} else {
			$this->message('Login unsuccessful.', 'error');
		}
	}