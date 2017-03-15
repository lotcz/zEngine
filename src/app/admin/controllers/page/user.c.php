<?php

	require_once __DIR__ . '/../../../models/user.m.php';	
	
	if (isset($_POST['user_id'])) {
		// save user values
		if ($_POST['user_id'] > 0) {
			$user = new UserModel($this->db, $_POST['user_id']);
		} else {
			$user = new UserModel($this->db);
		}
		$user->setData($_POST);
		unset($user->data['user_password']);
		$user->data['user_email'] = strtolower($_POST['user_email']);		
		if (isset($_POST['user_password']) && strlen($_POST['user_password']) > 0) {
			$user->data['user_password_hash'] = $this->z->auth->hashPassword($_POST['user_password']);
		}
		$user->save();
		$this->redirect('admin/users');
	} elseif ($this->getPath(-2) == 'edit') {
		$user = new UserModel($this->db, $this->getPath(-1));
		$this->setPageTitle('Editing Administrator');
	} elseif ($this->getPath(-2) == 'delete') {
		$user = new UserModel($this->db);
		$user->deleteById($this->getPath(-1));
		$this->redirect('admin/users');
	} else {
		$user = new UserModel($this->db);
		$this->setPageTitle('New Administrator');
	}
	
	$this->setData('user', $user);