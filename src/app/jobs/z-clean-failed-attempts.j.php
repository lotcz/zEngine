<?php

	if ($this->z->isModuleEnabled('security')) {
		$count = $this->z->security->resetFailedAttempts();
		echo "IP failed attempts were reset" . PHP_EOL;
	} else {
		echo "Security module disabled" . PHP_EOL;
	}
