<?php

	$user = $this->z->auth->user;
	$json = [];
	if ($user) {
		$json = $user->getJson();
	}
	$this->setData('json', $json);
