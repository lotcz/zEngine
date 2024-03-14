<?php

/**
* Module that handles cookies.
*/
class cookiesModule extends zModule {

	public $depends_on = ['resources'];

	public $show_warning = false;
	public $show_disabled = true;
	public $warning_confirmed_cookie_name = 'cookies_warning_confirmed';

	public function onEnabled() {
		$this->show_warning = $this->getConfigValue('show_warning', $this->show_warning);
		$this->show_disabled = $this->getConfigValue('show_disabled', $this->show_disabled);
		$this->warning_confirmed_cookie_name = $this->getConfigValue('warning_confirmed_cookie_name', $this->warning_confirmed_cookie_name);
	}

	function OnBeforeRender() {
		$this->z->core->includeCSS('resources/cookies.css');
		$this->z->core->includeJS('resources/cookies.js');
		$this->z->core->insertJS(
			[
				'z_cookies' => [
					'show_warning' => $this->show_warning,
					'show_disabled' => $this->show_disabled,
					'warning_confirmed_cookie_name' => $this->warning_confirmed_cookie_name
				]
			]
		);
		if ($this->show_warning) {
			$this->z->core->includePartial('cookies-warning', $this->getConfigValue('warning_placement', 'bottom'));
		}
		if ($this->show_disabled) {
			$this->z->core->includePartial('cookies-disabled', $this->getConfigValue('disabled_placement', 'bottom'));
		}
	}

	public function getCookie($cookie_name, $default_value = null) {
		return $_COOKIE[$cookie_name] ?? $default_value;
	}

	public function setCookie($cookie_name, $value, $expire, $path = '/', $domain = false, $secure = false) {
		if ($expire == null) {
			$expire = time() + (10 * 365 * 24 * 60 * 60); // 10 years
		}
		setcookie($cookie_name, $value, $expire, $path, $domain, $secure);
	}

	public function resetCookie($cookie_name, $path = '/', $domain = false, $secure = false) {
		unset($_COOKIE[$cookie_name]);
		$this->setCookie($cookie_name, '', time() - 3600, $path, $domain, $secure);
	}

}
