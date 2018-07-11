/**
* Change active language
*/
function changeLanguage(event, lang) {
	event.preventDefault();
	setCookie(z_i18n.language_cookie_name, lang, 365, '/');
	document.location = document.location;
}