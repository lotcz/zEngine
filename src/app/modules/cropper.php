<?php

class cropperModule extends zModule {

	private $registeredGroups = [];

	function onBeforeRender() {
		if (count($this->registeredGroups) > 0) {
			$this->z->core->includeCSS('resources/cropper/image-cropper.css', 'admin.head');
			$this->z->core->includeJS('resources/cropper/image-cropper.js', 'admin.bottom');
		}

		foreach ($this->registeredGroups as $inputGroupId) {
			$this->z->core->insertJS("imageCropperListen(
				document.querySelector('#$inputGroupId input[type=file]'),
				document.querySelector('#$inputGroupId img'),
				[[1030,900]]
			);",
				'admin.bottom'
			);
		}
	}

	function listenToInput($inputGroupId) {
		$this->registeredGroups[] = $inputGroupId;
	}

}
