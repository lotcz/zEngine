<form method="POST" id="form_login">
	<div class="form-group row">
		<label for="user_name" class="col-sm-1 col-form-label"><?=$this->t('Login')?></label>
		<div class="col-sm-3">
			<input name="user_name" id="user_name" maxlength="50" value="<?=z::get('user_name', '') ?>" class="form-control" type="text" required >
			<div class="form-validation" id="user_name_validation_length"><?=$this->t('Please enter your login name or e-mail.')?></div>
		</div>
	</div>
	<div class="form-group row">
		<label for="password" class="col-sm-1 col-form-label"><?=$this->t('Password')?></label>
		<div class="col-sm-3 form-field">
			<input name="password" id="password" maxlength="50" value="" class="form-control" type="password" required >
			<div class="form-validation" id="password_validation_length"><?=$this->t('Please enter your password.')?></div>
		</div>
	</div>
	<div class="form-group row">
		<div class="col-sm-3 offset-sm-1 form-field">
			<button id="login_button" onclick="javascript:validateLoginForm();return false;" class="btn btn-success" ><?=$this->t('Sign In') ?></button>			
		</div>
	</div>
	<div class="form-group row">
		<div class="col-sm-3 offset-sm-1 form-field">
			<a class="form-button" href="<?=$this->url('admin/forgotten-password', $this->raw_path)?><?=(isset($_POST['email']) && strlen($_POST['email']) > 0) ? '&email=' . $_POST['email'] : '' ?>"><?= $this->t('Forgotten Password') ?></a>
		</div>
	</div>
</form>

<script>
function validateLoginForm() {
	var frm = new formValidation('form_login');
	frm.add('user_name', 'length', '1');
	frm.add('password', 'length', '1');
	frm.submit();
}
</script>
