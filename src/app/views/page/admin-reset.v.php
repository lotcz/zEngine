<?php
	$show_form = $this->getData('show_form');
?>
<div class="inner cover">
		<?php
			if ($show_form) { ?>
				<form method="post" action="/admin/reset-password/<?=$data['user_id'] ?>?reset_token=<?=$data['reset_token'] ?>" class="form-horizontal" >
					<div class="form-group">
						<label for="password" class="col-sm-4 control-label"><?=$this->t('New Password') ?>:</label>
						<div class="col-sm-4">
							<input type="password" name="password" class="form-control"  />
						</div>
						<div class="col-sm-4 form-validation" id="password_validation"><?=$this->t('Required.') ?></div>
					</div>
					<div class="form-group">
						<label for="password" class="col-sm-4 control-label"><?=$this->t('Repeat New Password') ?>:</label>
						<div class="col-sm-4">
							<input type="password" name="password2" class="form-control"  />
						</div>
						<div class="col-sm-4 form-validation" id="password2_validation"><?=$this->t('Required.') ?></div>
						<div class="col-sm-4 form-validation" id="password_match_validation"><?=$this->t('Passwords don\'t match.') ?></div>
					</div>
					<div class="form-buttons">
						<input type="button" onclick="javascript:validate();" class="btn btn-success form-button" value="<?=$this->t('Reset Password') ?>">
					</div>
				</form>
		<?php } else { ?>
			<div class="form-buttons">
				<a class="form-button" href="/admin"><?=$this->t('Sign In') ?></a>
			</div>
		<?php } ?>
</div>

<script>
	function validate() {
		var isValid = true;
		isValid = validateField('password') && isValid;
		isValid = validateField('password2') && isValid;

		if (isValid) {
			if (document.forms[0]['password'].value == document.forms[0]['password2'].value) {
				hideFieldValidation('password_match');
			} else {
				showFieldValidation('password_match');
				isValid = false;
			}
		}

		if (isValid) {
			document.forms[0].submit();
		}
	}
</script>
