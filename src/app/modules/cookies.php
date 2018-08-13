<?php

/**
* Module that handles cookies.
*/
class cookiesModule extends zModule {

	public $depends_on = ['resources'];

	public function onEnabled() {
		$this->z->core->includeJS('resources/cookies.js');
	}

	public function getCookie($cookie_name, $default_value = null) {
		return $_COOKIE[$cookie_name] ?? $default_value;
	}

	public function setCookie($cookie_name, $value, $expire, $path = '/', $domain = false, $secure = false) {
		setcookie($cookie_name, $value, $expire, $path, $domain, $secure);
	}

	public function resetCookie($cookie_name, $path = '/', $domain = false, $secure = false) {
		unset($_COOKIE[$cookie_name]);
		$this->setCookie($cookie_name, '', time()-3600, $path, $domain, $secure);
	}

}
