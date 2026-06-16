<?php

class autocompleteModule extends zModule {

	public array $depends_on = ['db'];

	function onBeforeRender() {
		$this->z->core->includeJS('resources/autocomplete/autocomplete.js', 'admin.bottom');
		$this->z->core->includeCSS('resources/autocomplete/autocomplete.css', 'admin.head');
	}

	function getEndpointsConfig() {
		return $this->getConfigValue('endpoints', []);
	}

	function endpointExists($endpoint) {
		return isset($this->getEndpointsConfig()[$endpoint]);
	}

	function getEndpointConfig($endpoint) {
		if (!$this->endpointExists($endpoint)) {
			throw new Exception("Endpoint {$endpoint} does not exist!");
		}
		return $this->getEndpointsConfig()[$endpoint];
	}

}
