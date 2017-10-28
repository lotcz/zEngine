<?php

require_once __DIR__ . '/../app/models/static_page.m.php';

class staticpagesModule extends zModule {

	public function activateEditor() {
		$this->z->core->includeJS('resources/jHtmlArea-0.8.min.js');
		$this->z->core->includeJS('resources/staticpages.js');
		$this->z->core->includeCSS('resources/jHtmlArea.css');
	}

}
