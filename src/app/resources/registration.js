let register_form;
let email_field;

function register_validate(e) {
	e.preventDefault();	
	register_form.submit();
}

function register_getEmailValue() {
	return register_form.val(email_field.name);
}

function register_checkIfEmailExists() {
	z.fetch(`${z_email_check_ajax_url}?email=${register_getEmailValue()}`)
		.then(
			(response) => {
				const exists = (register_getEmailValue() === response.json.email) && response.json.exists;
				if (exists) {
					z.show('email_validation_exists');
					z.addClass('email_form_group', 'is-invalid');
				} else {
					z.hide('email_validation_exists');
				}
			}
		);
}

window.document.addEventListener(
	'DOMContentLoaded',
	() => {
		register_form = new formValidation('register_form');
		email_field = register_form.add('email', 'email');
		register_form.add('password', 'password');
		register_form.add('password_confirm', 'confirm', 'password');
		const email_input = z.getById('email');
		email_input.addEventListener('change', register_checkIfEmailExists);
		email_input.addEventListener('keyup', register_checkIfEmailExists);
	}
);
