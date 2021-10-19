<?php

	if ($this->controllers['master'] !== 'default') {
		$this->setMasterController('default');
		$this->runMasterController();
	}

	if ($this->controllers['main'] !== 'default') {
		$this->setMainController('default');
		$this->runMainController();
	}

	http_response_code(404);
