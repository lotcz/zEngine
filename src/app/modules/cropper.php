<?php

class cropperModule extends zModule {

	private $registered = [];

	function onBeforeRender() {
		if (count($this->registered) > 0) {
			$this->z->core->includeCSS('resources/cropper/image-cropper.css', 'admin.head');
			$this->z->core->includeJS('resources/cropper/image-cropper.js', 'admin.bottom');
		}

		foreach ($this->registered as $r) {
			$name = "z_cropper_" . $r['name'];
			$this->z->core->insertJS([$name => $r], 'admin.bottom');
			$this->z->core->insertJS("imageCropperListen(
					$name.input_element,
					$name.preview_element,
					$name.aspects,
					$name.max_size
				);",
				'admin.bottom'
			);
		}
	}

	function listenToFormField($fieldName, $aspects, $maxSize) {
		$inputGroupId = "{$fieldName}_form_group";
		$inputSelector = "#$inputGroupId input[type=file]";
		$previewSelector = "#$inputGroupId img";
		$this->registered[] = [
			'name' => $fieldName,
			'input_element' => $inputSelector,
			'preview_element' => $previewSelector,
			'aspects' => $aspects,
			'max_size' => $maxSize,
		];
	}

}
