<?php

	if ($this->getData('show_form')) {
		?>
			<form id="password_reset_form" method="POST" >
				<input type="hidden" name="reset_token" value="<?=$this->getData('reset_token') ?>" />
				<input type="hidden" name="user_email" value="<?=$this->getData('email') ?>" />
				<div class="form-group row">
					<label for="password" class="col-sm-2 col-form-label"><?=$this->t('New Password') ?>:</label>
					<div class="col-sm-3">
						<input type="password" id="password" name="password" class="form-control"  />
						<div class="form-validation" id="password_validation_password"><?=$this->t('Required.') ?></div>
					</div>
				</div>
				<div class="form-group row">
					<label for="password2" class="col-sm-2 col-form-label"><?=$this->t('Confirm New Password') ?>:</label>
					<div class="col-sm-3">
						<input type="password" id="password2" name="password2" class="form-control"  />
						<div class="form-validation" id="password2_validation_password"><?=$this->t('Required.') ?></div>
						<div class="form-validation" id="password2_validation_confirm"><?=$this->t('Passwords don\'t match.') ?></div>
					</div>
				</div>
				<div class="form-group row">
					<div class="col-sm-3 offset-sm-2">
						<input type="button" onclick="javascript:validateResetForm();return false;" class="btn btn-success" value="<?=$this->t('Reset Password') ?>">
					</div>
				</div>
			</form>
		<?php
	} else {
		?>
			<a href="<?=$this->url('login') ?>" class="btn btn-success"><?=$this->t('Sign In') ?></a>
		<?php
	}
?>

<script>
	function validateResetForm() {
		var frm = new formValidation('password_reset_form');
		frm.add('password', 'password');
		frm.add('password2', 'confirm', 'password');
		frm.submit();
	}
</script>
