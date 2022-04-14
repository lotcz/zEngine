<form method="POST" id="form_login">
	<div class="form-group">
		<label for="user_name" class="control-label col-form-label"><?=$this->t('E-mail')?>:</label>
		<div class="col-md-4 col-lg-3 form-field">
			<input name="email" id="email" maxlength="50" value="<?=z::get('email', '') ?>" class="form-control" type="text" required >
		</div>
		<div class="form-validation " id="email_validation_email"><?=$this->t('E-mail address is not in correct form! Please enter valid e-mail address.')?></div>
	</div>
	<div class="form-group">
		<label for="password" class="control-label col-form-label"><?=$this->t('Password')?>:</label>
		<div class="col-md-4 col-lg-3 form-field">
			<input name="password" id="password" maxlength="50" value="" class="form-control" type="password" required >
		</div>
		<div class="form-validation" id="password_validation_length"><?=$this->t('Please enter your password.')?></div>
	</div>
	<div class="form-group mt-3">
		<div class="form-field">
			<input name="path" id="path" value="custom" type="hidden" >
			<button type="submit" onclick="javascript:validateLoginForm();return false;" class="btn btn-success" ><?=$this->t('Sign In') ?></button>
		</div>
	</div>
	<div class="form-group mt-3">
		<div class="form-field">
			<a class="form-button" href="<?=$this->url('forgotten-password', $this->raw_path)?><?=(isset($_POST['email']) && strlen($_POST['email']) > 0) ? '&email=' . $_POST['email'] : '' ?>"><?= $this->t('Forgotten Password') ?></a>
		</div>
	</div>
</form>

<script>
function validateLoginForm() {
	var frm = new formValidation('form_login');
	frm.add('email', 'email');
	frm.add('password', 'length', '1');
	frm.submit();
}
</script>
