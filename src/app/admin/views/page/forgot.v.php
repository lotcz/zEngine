<div class="inner cover">
	<form method="post" action="/admin/forgotten-password" class="form-horizontal" >
		<div class="form-group">
			<label for="email" class="col-sm-4 control-label"><?=t('Login or E-mail') ?>:</label>
			<div class="col-sm-4"><input type="text" name="email" class="form-control" value="<?=(isset($_GET['email'])) ? $_GET['email'] : '' ?>" /></div>
			<div class="col-sm-4 form-validation" id="email_validation"><?= t('Required.') ?></div>
		</div>		
		<div class="form-buttons">
			<a class="form-button" href="/admin"><?=t('Sign In') ?></a>
			<input type="button" onclick="javascript:validate();" class="btn btn-success form-button" value="<?=t('Reset Password') ?>">
		</div>
	</form>	
</div>

<script>
	function validate() {
		var isValid = true;
		isValid = validateField('email') && isValid;
		
		if (isValid) {
			document.forms[0].submit();
		}
	}
</script>