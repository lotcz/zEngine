<?php
	
	return [
		// name of cookie used to store user session token
		'cookie_name' => 'user_session_token',
		
		// maximum number of login attempts before account is locked
		'max_attempts' => 100,
		
		'min_password_length' => 5,
		
		//time interval in seconds after which session will expire
		'session_expire' => 60*60*24*7, //7 days
	
		//time interval in seconds after which reset password will expire 
		'reset_password_expires' => 60*60*24*7 //7 days
	];