<?php

	$json = $this->z->auth->isAuth() ? $this->z->auth->user->getJson() : null;
	$this->setData('json', $json);
