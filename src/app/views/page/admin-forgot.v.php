<div class="inner cover">
	<form method="post" class="form-horizontal" id="admin_forgot_form" >
		<div class="form-group">
			<label for="email" class="col-sm-4 control-label"><?=$this->t('Login or E-mail') ?>:</label>
			<div class="col-sm-4"><input type="text" name="email" class="form-control" value="<?=(isset($_GET['email'])) ? $_GET['email'] : '' ?>" /></div>
			<div class="col-sm-4 form-validation" id="email_validation_email"><?=$this->t('Required.') ?></div>
		</div>
		<div class="form-buttons">
			<a class="form-button" href="<?=$this->url('admin') ?>"><?=$this->t('Sign In') ?></a>
			<input type="button" onclick="javascript:admin_forgot_validate();return false;" class="btn btn-success form-button" value="<?=$this->t('Reset Password') ?>">
		</div>
	</form>
</div>

<script>
function admin_forgot_validate() {
		var frm = new formValidation('admin_forgot_form');
		frm.add('email', 'email');
		frm.submit();
	}
</script>
