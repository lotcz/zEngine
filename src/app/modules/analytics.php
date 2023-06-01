<?php

class analyticsModule extends zModule {

	public $id = null;

	public function onEnabled() {
		$this->id = $this->getConfigValue('id');
	}

	function OnBeforeRender() {
		if ($this->z->isDebugMode()) {
			$this->z->core->insertJS('console.log(\'Analytics disabled in debug mode. GA-ID: ' . $this->id . '\');', 'bottom');
			return;
		}

		$this->z->core->includeJS('https://www.googletagmanager.com/gtag/js?id=' . $this->id, 'head');
		$this->z->core->insertJS(
			"window.dataLayer = window.dataLayer || [];
				function gtag(){dataLayer.push(arguments);}
				gtag('js', new Date());
				gtag('config', '$this->id');
			",
			'head'
		);
	}

}
