function validateLoginForm() {
	var frm = new formValidation('form_login');
	frm.add('user_name', 'length', '1');
	frm.add('password', 'length', '1');
	frm.submit();
}		