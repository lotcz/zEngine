<?php

/**
* Module for TinyMCe wysiwyg editor integration.
* Use tinymce form field type or textarea with .tinymce class.
*/
class tinymceModule extends zModule {

	public $tinymce_config = [];

	public function onEnabled() {
		$this->tinymce_config = $this->getConfigValue('tinymce_conf', []);

		// process default tinymce includes
		$includes = $this->getConfigValue('includes', []);
		foreach ($includes as $include) {
			$this->z->core->addToIncludes($include[0], $include[1],  $include[2]);
		}
	}

	public function activateTinyMce($placement = 'admin.bottom') {
		$this->z->core->insertJS(['z_tinymceconfig' => $this->tinymce_config], $placement);
		$this->z->core->insertJS('tinymce.init(z_tinymceconfig);', $placement);
	}

	public function setTinyMceConfig($config) {
		$this->tinymce_config = $config;
	}

	public function onBeforeRender() {
		$this->activateTinyMce();
	}

}
