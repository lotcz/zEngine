<?php

	if (!$this->z->isModuleEnabled('newsletter')) {
		echo "Newsletter module disabled!" . PHP_EOL;
		die();
	}

	$deleted = $this->z->newsletter->cleanSubscriptionEmails();
	echo "Deleted <strong>$deleted</strong> invalid subscription emails." . PHP_EOL;
