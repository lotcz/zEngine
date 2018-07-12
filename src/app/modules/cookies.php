<?php

/**
* Module that handles cookies.
*/
class cookiesModule extends zModule {

	public function onEnabled() {
		$this->requireModule('resources');
		$this->z->core->includeJS('resources/cookies.js');		
	}
		
}
