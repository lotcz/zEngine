$(function() {
	if (z_cookies.show_disabled) {
		if (!cookiesEnabled()) {
			$('#cookies_disabled').show();
		}
	}
	if (z_cookies.show_warning) {
		if (cookiesEnabled() && !cookiesWarningConfirmed()) {
			$('#cookies_warning').show();
		}
	}
});

function cookiesConfirmWarning() {
	$('#cookies_warning').hide();
	setCookie(z_cookies.warning_confirmed_cookie_name, '1');
	z_cookies.cookies_warning_confirmed = true;
}

function cookiesWarningConfirmed() {
	if (z_cookies.cookies_warning_confirmed == null) {
		let cookieVal = getCookie(z_cookies.warning_confirmed_cookie_name);
		z_cookies.cookies_warning_confirmed = (cookieVal == '1');
	}
	return z_cookies.cookies_warning_confirmed;
}

/**
* Check if browser supports cookies.
*/
function cookiesEnabled() {
	if (z_cookies.browser_cookies_enabled == null) {
		if (navigator.cookieEnabled) {
			z_cookies.browser_cookies_enabled = true;
		} else {
			document.cookie = "cookietest=1";
			z_cookies.browser_cookies_enabled = document.cookie.indexOf("cookietest=") != -1;
			document.cookie = "cookietest=1; expires=Thu, 01-Jan-1970 00:00:01 GMT";
		}
	}
	return z_cookies.browser_cookies_enabled;
}

/**
* Set cookie value
*/
function setCookie(cname, cvalue, exdays, path) {
	var d = new Date();
	d.setTime(d.getTime() + (exdays*24*60*60*1000));
	var expires = 'expires='+d.toUTCString();
	document.cookie = cname + '=' + cvalue + '; ' + expires + ';path=' + path;
}

/**
* reset cookie value
*/
function resetCookie(cname, path) {
	setCookie(cname, '', -1, path);
}

/**
* Get cookie value
*/
function getCookie(cname) {
	var name = cname + '=';
	var ca = document.cookie.split(';');
	for(var i=0; i<ca.length; i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1);
		if (c.indexOf(name) == 0) return c.substring(name.length,c.length);
	}
	return '';
}
