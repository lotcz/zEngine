var register_form;
var email_field;

function initializeForm() {
	register_form = new formValidation('register_form');
	email_field = register_form.add('email', 'email');
	register_form.add('password', 'password');
	register_form.add('password_confirm', 'confirm', 'password');
}

function register_validate(e) {
	e.preventDefault();	
	register_form.submit();
}

function getEmailValue() {
	return register_form.val(email_field.name);
}

function emailChecked(response) {
	var exists = (getEmailValue() == response.email && response.exists);
	if (exists) {
		register_form.validate(email_field);
	}
	register_form.show_validation('email', 'exists', (!exists));
}

function checkIfEmailExists() {
	$.getJSON(z_email_check_ajax_url,
		{
			email: getEmailValue()
		},
		emailChecked
	);
}

$(function() {
	initializeForm();
	$('#email').change(checkIfEmailExists);
	$('#email').keyup(checkIfEmailExists);
});