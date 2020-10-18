<?php

/**
* Module for TinyMCe wysiwyg editor integration.
* Use tinymce form field type or textarea with .tinymce class.
* Then call $this->z->tinymce->include();
*/
class tinymceModule extends zModule {

	public function include($tinymce_config = null, $placement = null) {

		if ($tinymce_config == null) {
			$tinymce_config = $this->getConfigValue('tinymce_conf', []);
		}

		if ($placement == null) {
			$placement = $this->getConfigValue('default_placement', []);
		}

		// process default tinymce includes
		$includes = $this->getConfigValue('includes', []);
		foreach ($includes as $include) {
			$this->z->core->addToIncludes(($include[1]) ? $include[0] : $this->z->core->url($include[0]), $include[2], $placement);
		}

		$this->z->core->insertJS(['z_tinymceconfig' => $tinymce_config], $placement);
		$this->z->core->insertJS('$("textarea.wysiwyg").tinymce(z_tinymceconfig);', $placement);
	}

}
