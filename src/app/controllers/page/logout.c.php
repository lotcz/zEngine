<?php

	if ($this->z->auth->isAuth() && !$this->z->auth->isAnonymous()) {
		$this->z->auth->logout();
	}

	$this->redirectBack($this->z->auth->public_login_home);
