$(function() {
	if (!checkCookies()) {
		$('#cookies_disabled').show();
	}
});

/**
* Check if browser supports cookies.
*/
function checkCookies() {
	if (navigator.cookieEnabled) return true;
	document.cookie = "cookietest=1";
	var ret = document.cookie.indexOf("cookietest=") != -1;
	document.cookie = "cookietest=1; expires=Thu, 01-Jan-1970 00:00:01 GMT";
	return ret;
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
