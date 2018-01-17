<?php

require_once __DIR__ . '/../app/models/static_page.m.php';

/**
* Module that handles static pages feature.
*/
class staticpagesModule extends zModule {

	public function activateEditor() {
		$this->z->core->includeJS('resources/jhtmlarea/jhtmlarea.min.js');
		$this->z->core->includeJS('resources/staticpages.js');
		$this->z->core->includeCSS('resources/jhtmlarea/jhtmlarea.css');
	}

}
