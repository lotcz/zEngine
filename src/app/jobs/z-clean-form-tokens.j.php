<?php

	if ($this->z->isModuleEnabled('forms')) {
		$this->z->forms->deleteExpiredFormTokens();
		echo 'Deleted expired form tokens.' . PHP_EOL;
	} else {
		echo "Forms module disabled" . PHP_EOL;
	}
