<?php

if ($this->z->isModuleEnabled('emails')) {
	$job = $this->z->emails->getSendEmailsAsyncJob();
	$job->execute();
} else {
	echo "Emails module disabled" . PHP_EOL;
}
