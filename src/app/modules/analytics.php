<?php

class analyticsModule extends zModule {

	public $id = null;

	public function onEnabled() {
		$this->id = $this->getConfigValue('id');
	}

	function OnBeforeRender() {
		$this->z->core->includeJS('https://www.googletagmanager.com/gtag/js?id=' . $this->id, true, 'head');
		$this->z->core->insertJS(
			"
				window.dataLayer = window.dataLayer || [];
             	function gtag(){dataLayer.push(arguments);}
             	gtag('js', new Date());
             	gtag('config', '$this->id');
            ",
            'head'
		);
	}

}
