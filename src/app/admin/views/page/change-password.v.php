<form id="changepass_form" method="POST">	
	<div class="form-group row">
		<label for="password" class="col-sm-2 col-form-label"><?=$this->t('New Password') ?></label>
		<div class="col-sm-3">
			<input type="password" id="password" name="password" aria-describedby="passwordHelp" class="form-control"  />
			<small id="passwordHelp" class="form-text text-muted"><?=$this->t('Password must be at least %d characters long.', $this->z->auth->getConfigValue('min_password_length')) ?></small>			
			<div class="form-validation" id="password_validation_length"><?=$this->t('Password must be at least %d characters long.', $this->z->auth->getConfigValue('min_password_length')) ?></div>
		</div>					
	</div>
	<div class="form-group row">
		<label for="password_confirm" class="col-sm-2 col-form-label"><?=$this->t('Confirm New Password') ?></label>
		<div class="col-sm-3">
			<input type="password" id="password_confirm" name="password_confirm" class="form-control"  />
			<div class="form-validation" id="password_confirm_validation_password"><?=$this->t('Required.') ?></div>
			<div class="form-validation" id="password_confirm_validation_confirm"><?=$this->t('Passwords don\'t match.') ?></div>
		</div>					
	</div>
	<div class="form-group row">
		<div class="col-sm-3 offset-sm-2">
			<input type="button" onclick="javascript:changepass_validate(event);return false;" class="btn btn-success" value="<?=$this->t('Change Password') ?>">
		</div>
	</div>
</form>

<script>
	function changepass_validate(e) {
		e.preventDefault();
		var frm = new formValidation('changepass_form');
		frm.add('password', 'length', <?=$this->z->auth->getConfigValue('min_password_length') ?>);
		frm.add('password_confirm', 'confirm', 'password');
		frm.submit();
	}
</script>