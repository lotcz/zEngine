<?php

	// 10 minutes should also be the minimum cron interval to prevent parallel processing.
	set_time_limit(60 * 10);

	$this->z->emails->processQueue();
