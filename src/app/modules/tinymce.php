<?php

/**
* Module for TinyMCe wysiwyg editor integration.
* Use tinymce form field type or textarea with .tinymce class.
*/
class tinymceModule extends zModule {

	public $tinymce_config = [];
	public $placement = 'admin.head';

	public function onEnabled() {
		$this->tinymce_config = $this->getConfigValue('tinymce_conf', []);
		$this->placement = $this->getConfigValue('default_placement', 'admin.head');

		// process default tinymce includes
		$includes = $this->getConfigValue('includes', []);
		foreach ($includes as $include) {
			$this->z->core->addToIncludes(($include[1]) ? $include[0] : $this->z->core->url($include[0]), $include[2], $this->placement);
		}
	}
	
	public function activateTinyMce() {
		$this->z->core->insertJS(['z_tinymceconfig' => $this->tinymce_config], $this->placement);
		$this->z->core->insertJS('$("textarea.wysiwyg").tinymce(z_tinymceconfig);', $this->placement);
	}
	
	public function setTinyMceConfig($config) {
		$this->tinymce_config = $config;
	}

	public function onBeforeRender() {
		$this->activateTinyMce();
	}

}
