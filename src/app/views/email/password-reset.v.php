<?php
	$show_form = $this->getData('show_form');
	$reset_token = $this->getData('reset_token');
	$customer_email = $this->getData('email');
?>
<div class="inner cover">
		<?php
			if ($show_form) { ?>
				<form id="reset-form" method="post" class="form-horizontal" >
					<input type="hidden" name="reset_token" value="<?=$reset_token ?>" />
					<input type="hidden" name="email" value="<?=$customer_email ?>" />
					<div class="form-group">
						<label for="password" class="col-sm-4 control-label"><?=$this->t('New Password') ?>:</label>
						<div class="col-sm-4">
							<input type="password" name="password" class="form-control"  />
						</div>
						<div class="col-sm-4 form-validation" id="password_validation_password"><?=$this->t('Required.') ?></div>
					</div>
					<div class="form-group">
						<label for="password" class="col-sm-4 control-label"><?=$this->t('Repeat New Password') ?>:</label>
						<div class="col-sm-4">
							<input type="password" name="password2" class="form-control"  />
						</div>
						<div class="col-sm-4 form-validation" id="password2_validation_password"><?=$this->t('Required.') ?></div>
						<div class="col-sm-4 form-validation" id="password2_validation_confirm"><?=$this->t('Passwords don\'t match.') ?></div>
					</div>
					<div class="form-buttons">
						<input type="button" onclick="javascript:validateResetForm();return false;" class="btn btn-success form-button" value="<?=$this->t('Reset Password') ?>">
					</div>
				</form>
		<?php } else { ?>
			<div class="form-buttons">
				<a class="form-button" href="<?=$this->url('login') ?>"><?=$this->t('Sign In') ?></a>
			</div>
		<?php } ?>
</div>

<script>
	function validateResetForm() {
		var frm = new formValidation('reset-form');
		frm.add('password', 'password');
		frm.add('password2', 'confirm', 'password');
		frm.submit();
	}
</script>
