<?php

	if ($this->z->isModuleEnabled('emails')) {
		$count = $this->z->emails->cleanSentEmails();
		echo "Deleted <strong>$count</strong> old email backups" . PHP_EOL;
	} else {
		echo "Emails module disabled" . PHP_EOL;
	}
