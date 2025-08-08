<?php

	$email = z::get('email');
	$json = [];
	$json['email'] = $email;
	$json['exists'] = false;
	
	if (isset($email) && strlen($email) > 0) {
		$customer = new UserModel($this->z->db);
		$customer->loadByLoginOrEmail($email);
		$json['exists'] = $customer->is_loaded;	
	}
	
	$this->setData('json', $json);
