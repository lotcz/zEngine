function legalageFallbackRedirect() {
	document.location = z_legalage.fallback_url;
}

function legalageIsConfirmed() {
	if (z_legalage.confirmed == '1') {
		return true;
	}
	const cookieval = getCookie(z_legalage.language_cookie_name);
	return (cookieval == '1');
}

function legalageSetIsConfirmed(isConfirmed) {
	const val = isConfirmed ? '1' : '0';
	z_legalage.confirmed = val;
	setCookie(z_legalage.cookie_name, val, 365, '/');
}
