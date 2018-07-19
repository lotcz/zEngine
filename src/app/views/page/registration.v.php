<form method="post" id="register_form" class="form-horizontal" >
	<div class="form-group row">
		<label for="full_name" class="col-sm-2 control-label"><?=$this->t('Full name') ?>:</label>
		<div class="col-sm-4">
			<input type="text" id="full_name" name="full_name" class="form-control" value="<?=$this->xssafe(z::get('full_name')) ?>" />
		</div>		
	</div>
	<div class="form-group row">
		<label for="email" class="col-sm-2 control-label"><?=$this->t('E-mail') ?>:</label>
		<div class="col-sm-4">
			<input type="text" id="email" name="email" class="form-control" value="<?=$this->xssafe(z::get('email')) ?>" required />
		</div>
		<div class="col-sm-6 form-validation" id="email_validation_email"><?=$this->t('E-mail address is not in correct form! Please enter valid e-mail address.') ?></div>
		<div class="col-sm-6 form-validation" id="email_validation_exists"><?=$this->t('This email is already used!') ?></div>
	</div>
	<div class="form-group row">
		<label for="password" class="col-sm-2 control-label"><?=$this->t('Heslo') ?>:</label>
		<div class="col-sm-4">
			<input type="password" id="password" name="password" class="form-control" required />
		</div>
		<div class="col-sm-6 form-validation" id="password_validation_password"><?=$this->t('Password must be at least %d characters long.', $this->z->auth->getConfigValue('min_password_length')) ?></div>
	</div>
	<div class="form-group row">
		<label for="password_confirm" class="col-sm-2 control-label"><?=$this->t('Confirm Password') ?>:</label>
		<div class="col-sm-4">
			<input type="password" id="password_confirm" name="password_confirm" class="form-control" required />
		</div>
		<div class="col-sm-6 form-validation" id="password_confirm_validation_confirm"><?=$this->t('Passwords don\'t match.') ?></div>
	</div>
	<div class="form-group row">
		<div class="col-sm-3 offset-sm-2 form-field">
			<input type="submit" onclick="javascript:register_validate(event);" class="btn btn-success form-button" value="<?=$this->t('Register') ?>" />
		</div>
	</div>
	<div class="form-group row">
		<div class="col-sm-3 offset-sm-2 form-field">
			<a class="form-button" href="<?=$this->url('login')?>"><?=$this->t('Sign In') ?></a>
		</div>
	</div>	
</form>