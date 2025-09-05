<form method="POST" id="form_login">
	<div id="email_form_group" class="form-group row">
		<label for="user_name" class="control-label col-sm-2"><?=$this->t('E-mail')?>:</label>
		<div class="col-sm-4 orm-field">
			<input name="email" id="email" maxlength="50" value="<?=z::get('email', '') ?>" class="form-control" type="text" required >
		</div>
		<div class="form-validation " id="email_validation_email"><?=$this->t('E-mail address is not in correct form! Please enter valid e-mail address.')?></div>
	</div>
	<div id="password_form_group" class="form-group row">
		<label for="password" class="control-label col-sm-2"><?=$this->t('Password')?>:</label>
		<div class="col-sm-4 form-field">
			<input name="password" id="password" maxlength="50" value="" class="form-control" type="password" required >
		</div>
		<div class="form-validation" id="password_validation_length"><?=$this->t('Please enter your password.')?></div>
	</div>
	<div class="form-group mt-3 row">
		<div class="form-field offset-sm-2">
			<button type="submit" onclick="javascript:validateLoginForm(event);return false;" class="btn btn-success" ><?=$this->t('Sign In') ?></button>
		</div>
	</div>
	<div class="form-group row">
		<div class="form-field offset-sm-2">
			<div class="d-flex align-items-center gap-4">
				<a class="form-button" href="<?=$this->url('forgotten-password', $this->raw_path)?><?=(isset($_POST['email']) && strlen($_POST['email']) > 0) ? '&email=' . $_POST['email'] : '' ?>"><?= $this->t('Forgotten Password') ?></a>
				<a class="form-button" href="<?=$this->url('registration', $this->raw_path)?><?=(isset($_POST['email']) && strlen($_POST['email']) > 0) ? '&email=' . $_POST['email'] : '' ?>"><?= $this->t('Register') ?></a>
			</div>
		</div>
	</div>
</form>

<script>
function validateLoginForm(e) {
	e.preventDefault();
	var frm = new formValidation('form_login');
	frm.add('email', 'email');
	frm.add('password', 'length', '1');
	frm.submit();
}
</script>
