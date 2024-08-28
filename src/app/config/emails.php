<?php

	return [

		// default e-mail address from which emails will be sent
		'from_address' => 'your@site.email',

		// applies to cron queue processing
		'limit_emails_per_cron' => 2,

		// sent emails will be deleted after this amount of days
		// 0 = never delete sent emails
		'keep_sent_emails_days' => 30

	];
