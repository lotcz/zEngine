<?php

/**
 * Module that handles legal age confirmation with cookies.
 */
class legalageModule extends zModule {

	public array $depends_on = ['cookies'];

	public $cookie_name = 'legal_age_confirmed';

	public $fallback_url = '/';

	public function onEnabled() {
		$this->cookie_name = $this->getConfigValue('cookie_name', $this->cookie_name);
		$this->fallback_url = $this->z->core->url('');
	}

	function renderConfirmDialog() {
		$this->z->core->includeJS('resources/legalage.js');
		$this->z->core->insertJS(
			[
				'z_legalage' => [
					'cookie_name' => $this->cookie_name,
					'fallback_url' => $this->fallback_url
				]
			]
		);
		$this->z->core->includePartial('legalage-confirm', 'bottom');
	}

	public function isLegalAgeConfirmed(): bool {
		$cookievalue = $this->z->cookies->getCookie($this->cookie_name, '0');
		return ($cookievalue == '1');
	}

	public function setLegalAgeConfirmed(bool $confirmed) {
		$this->z->cookies->setCookie($this->cookie_name, $confirmed ? '1' : '0');
	}

	public function requireLegalAge() {
		if (!$this->isLegalAgeConfirmed()) {
			$this->renderConfirmDialog();
		}
	}

}
