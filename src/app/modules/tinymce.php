<?php

/**
* Module for TinyMCe wysiwyg editor integration.
* Use tinymce form field type or textarea with .tinymce class.
*/
class tinymceModule extends zModule {

	public $tinymce_config = [];

	public $tinymce_paste_preprocess = null;
	public $tinymce_paste_postprocess = null;

	public function onEnabled() {
		$this->tinymce_config = $this->getConfigValue('tinymce_conf', []);
		$this->tinymce_paste_preprocess = $this->getConfigValue('tinymce_paste_preprocess');
		$this->tinymce_paste_postprocess = $this->getConfigValue('tinymce_paste_postprocess');

		// process default tinymce includes
		$includes = $this->getConfigValue('includes', []);
		foreach ($includes as $include) {
			$this->z->core->addToIncludes($include[0], $include[1],  $include[2]);
		}
	}

	public function onBeforeRender() {
		$this->activateTinyMce();
	}

	public function activateTinyMce($placement = 'admin.bottom') {
		$this->z->core->insertJS(['z_tinymceconfig' => $this->tinymce_config], $placement);
		if ($this->tinymce_paste_preprocess !== null) {
			$this->z->core->insertJS(sprintf('z_tinymceconfig.paste_preprocess = %s;', $this->tinymce_paste_preprocess), $placement);
		}
		if ($this->tinymce_paste_postprocess !== null) {
			$this->z->core->insertJS(sprintf('z_tinymceconfig.paste_postprocess = %s;', $this->tinymce_paste_postprocess), $placement);
		}
		$this->z->core->insertJS('tinymce.init(z_tinymceconfig);', $placement);
	}

	public function setTinyMceConfig($config) {
		$this->tinymce_config = $config;
	}

	public function getTinyMceConfig() {
		return $this->tinymce_config;
	}

	public function addToConfig($config) {
		$this->setTinyMceConfig(z::mergeAssocArrays($this->getTinyMceConfig(), $config));
	}

}
