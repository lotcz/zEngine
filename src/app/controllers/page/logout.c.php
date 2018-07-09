<?php

	if ($this->isCustAuth() && !$this->z->custauth->isAnonymous()) {
		$this->z->custauth->logout();
	}
	
	$this->redirectBack('');