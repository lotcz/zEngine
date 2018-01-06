<?php
	if (z::get('security_token','') == $this->getConfigValue('security_token')) {
		phpinfo();
	} else {
		die('Wrong security token.');
	}