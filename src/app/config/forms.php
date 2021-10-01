<?php

	return [

		// if true, then all forms will be automatically protected by tokens
		'protection_enabled' => true,

		// protection token will not be valid before this amount of seconds passed (brute force attack protection)
		'protection_token_min_delay' => 1, // 1 second

		// protection tokens will expire after this amount of seconds
		'protection_token_expires' => 60*60, // one hour

	];
