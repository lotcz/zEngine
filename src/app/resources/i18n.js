/**
* Change active language
*/
function changeLanguage(language_id) {
	setCookie(z_i18n.language_cookie_name, language_id, 365, '/');
	document.location.reload(); 
}
