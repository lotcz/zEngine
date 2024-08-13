<?php

require_once __DIR__ . "/../classes/send-email-async-job.php";

if ($this->z->isModuleEnabled('emails')) {
	$aj = $this->z->emails->getSendEmailsAsyncJob();
	$aj->execute();
} else {
	echo "Emails module disabled" . PHP_EOL;
}


