<?php

require_once __DIR__ . '/../models/static_page.m.php';

/**
* Module that handles static pages feature.
*/
class staticpagesModule extends zModule {

	public function onEnabled() {
		$this->requireModule('alias');
	}
	
	public function activateEditor() {
		$this->z->core->includeJS('resources/jhtmlarea/jhtmlarea.min.js');
		$this->z->core->includeJS('resources/staticpages.js');
		$this->z->core->includeCSS('resources/jhtmlarea/jhtmlarea.css');
	}

}
