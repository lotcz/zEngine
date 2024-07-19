<?php

	if ($this->z->isModuleEnabled('emails')) {
		$sent = $this->z->emails->processQueue();
		echo "Sent $sent emails." . PHP_EOL;
	} else {
		echo "Emails module disabled" . PHP_EOL;
	}


