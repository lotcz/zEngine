<form method="POST" class="form-horizontal" id="form_login">

	<div class="row">
		<div class="col-sm-6 col-sm-offset-3">
			<div class="panel panel-default">
				<div class="panel-heading">
					<h3 class="panel-title"><?=$this->t('Administrator login') ?></h3>
				</div>
				<div class="panel-body">
					<div id="user_name_form_group" class="form-group">
						<label for="user_name" class="col-sm-4 control-label form-label"><?=$this->t('Login')?></label>
						<div class="col-sm-8 form-field">
							<input name="user_name" id="user_name" maxlength="50" value="" class="form-control" type="text" required >
							<div class="form-validation" id="user_name_validation_length"><?=$this->t('Please enter your login name or e-mail.')?></div>
						</div>
					</div>
					<div id="password_form_group" class="form-group">
						<label for="password" class="col-sm-4 control-label form-label"><?=$this->t('Password')?></label>
						<div class="col-sm-8 form-field">
							<input name="password" id="password" maxlength="50" value="" class="form-control" type="password" required >
							<div class="form-validation" id="password_validation_length"><?=$this->t('Please enter your password.')?></div>
						</div>
					</div>
					<div id="password_form_group" class="form-group">
						<div class="col-sm-8 col-sm-offset-4 form-field">
							<button onclick="javascript:validateLoginForm();return false;" class="form-control btn btn-success" ><?=$this->t('Sign In') ?></button>
							<a class="form-button" href="<?=$this->url('admin-forgot', $this->raw_path)?><?=(isset($_POST['email']) && strlen($_POST['email']) > 0) ? '&email=' . $_POST['email'] : '' ?>"><?= $this->t('Forgotten Password') ?></a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

</form>
