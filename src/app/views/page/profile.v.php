<form id="form_user" method="POST">
  <?php
    $this->z->forms->renderForm($this->getData('form'));
  ?>
  <div class="form-group row">
		<div class="col-sm-12">
			<a class="btn btn-link" href="<?=$this->url($this->z->auth->public_login_home) ?>"><?=$this->t('Back') ?></a>
			<button type="button" onclick="javascript:validateForm_user(event);return false;" class="btn btn-success"><?=$this->t('Save') ?></button>
     		<a class="btn btn-warning" href="<?=$this->url('change-password') ?>"><?=$this->t('Change Password') ?></a>
		</div>
	</div>
</form>
