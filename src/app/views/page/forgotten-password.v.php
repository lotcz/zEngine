<form method="POST" id="admin_forgot_form" >
	<div class="form-group row">
		<label for="email" class="col-sm-1 col-form-label"><?=$this->t('E-mail') ?>:</label>
		<div class="col-sm-3">
			<input type="text" id="email" name="email" class="form-control" value="<?=z::get('email','') ?>" />
		</div>
		<div class="form-validation col-sm-11 offset-sm-1" id="email_validation_email"><?=$this->t('E-mail address is not in correct form! Please enter valid e-mail address.') ?></div>
	</div>
	<div class="form-group row">
		<div class="col-sm-3 offset-sm-1 form-field">
			<input type="submit" onclick="javascript:admin_forgot_validate(event);" class="btn btn-success form-button" value="<?=$this->t('Reset Password') ?>" />
		</div>
	</div>
	<div class="form-group row">
		<div class="col-sm-3 offset-sm-1 form-field">
			<a class="form-button" href="<?=$this->url('admin') ?>"><?=$this->t('Sign In') ?></a>
		</div>
	</div>
</form>

<script>
	function admin_forgot_validate(e) {
		e.preventDefault();
		var frm = new formValidation('admin_forgot_form');
		frm.add('email', 'email');
		frm.submit();
	}
</script>
