<?php

	$email = z::get('email');
	$json = [];
	$json['email'] = $email;
	$json['exists'] = false;
	
	if (isset($email) && strlen($email) > 0) {
		$customer = new CustomerModel($this->db);
		$customer->loadByEmail($email);
		$json['exists'] = $customer->is_loaded;	
	}
	
	$this->setData('json', $json);