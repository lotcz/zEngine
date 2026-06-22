<?php

	return [

		// if an IP exceeds this number of failed attempts, it will be banned
		'max_failed_attempts' => 100,

		// number of failed attempts will be reset after this period, leave empty for no reset
		// https://www.php.net/manual/en/dateinterval.createfromdatestring.php
		'reset_failed_attempts_after' => "P7D"

	];
