<?php

	// 5 minutes should be the minimum cron interval to prevent parallel processing.
	set_time_limit(60 * 5);

	$sent = $this->z->emails->processQueue();
	echo "Sent $sent emails.";
