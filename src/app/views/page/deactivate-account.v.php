<p>
	Mrzí nás, že odcházíte, ale když to musí být, tak se nedá nic dělat.
</p>
<p>
	Napište váš email to boxu níže: <span class="fst-italic"><?=$this->z->auth->getUser()->val('user_email')?></span>
</p>
<div class="alert alert-danger">
	Tuto akci nelze vrátit zpět!
</div>
<form method="post" id="deactivation_form" >
	<div class="form-group row" id="email_form_group">
		<label for="email" class="col-sm-2 control-label"><?=$this->t('E-mail') ?>:</label>
		<div class="col-sm-4">
			<input type="text" id="email" name="email" class="form-control" value="<?=$this->xssafe(z::get('email')) ?>" required />
		</div>
		<div class="form-validation" id="email_validation_email"><?=$this->t('E-mail address is not in correct form! Please enter valid e-mail address.') ?></div>
	</div>

	<div class="form-group row">
		<div class="offset-sm-4">
			<button type="submit"><?=$this->t('Deactivate')?></button>
		</div>
	</div>

</form>
