<?php

require_once __DIR__ . '/../app/models/static_page.m.php';

class staticpagesModule extends zModule {

	public function activateEditor() {
		$this->z->core->includeJS('resources/jhtmlarea.min.js');
		$this->z->core->includeJS('resources/staticpages.js');
		$this->z->core->includeCSS('resources/jhtmlarea.css');
	}

}
