<form method="POST" id="form_login">
	<div class="form-group row">
		<label for="user_name" class="col-sm-1 col-form-label"><?=$this->t('E-mail')?>:</label>
		<div class="col-sm-3">
			<input name="email" id="email" maxlength="50" value="<?=z::get('email', '') ?>" class="form-control" type="text" required >
		</div>
		<div class="form-validation col-sm-11 offset-sm-1" id="email_validation_email"><?=$this->t('E-mail address is not in correct form! Please enter valid e-mail address.')?></div>
	</div>
	<div class="form-group row">
		<label for="password" class="col-sm-1 col-form-label"><?=$this->t('Password')?>:</label>
		<div class="col-sm-3 form-field">
			<input name="password" id="password" maxlength="50" value="" class="form-control" type="password" required >
		</div>
		<div class="form-validation col-sm-11 offset-sm-1" id="password_validation_length"><?=$this->t('Please enter your password.')?></div>
	</div>
	<div class="form-group row">
		<div class="col-sm-3 offset-sm-1 form-field">
			<button onclick="javascript:validateLoginForm();return false;" class="btn btn-success" ><?=$this->t('Sign In') ?></button>
		</div>
	</div>
	<div class="form-group row">
		<div class="col-sm-3 offset-sm-1 form-field">
			<a class="form-button" href="<?=$this->url('forgotten-password', $this->raw_path)?><?=(isset($_POST['email']) && strlen($_POST['email']) > 0) ? '&email=' . $_POST['email'] : '' ?>"><?= $this->t('Forgotten Password') ?></a>
		</div>
	</div>
	<div class="form-group row">
		<div class="col-sm-3 offset-sm-1 form-field">
			<a class="form-button" href="<?=$this->url('registration', $this->raw_path)?><?=(isset($_POST['email']) && strlen($_POST['email']) > 0) ? '&email=' . $_POST['email'] : '' ?>"><?= $this->t('Registration') ?></a>
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
