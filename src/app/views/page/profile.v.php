<form id="form_customer" method="POST">
  <?php
    $this->z->forms->renderForm($this->getData('form'));
  ?>
  <div class="form-group row">
		<div class="col-sm-12">
			<button type="button" onclick="javascript:validateForm_customer(event);return false;" class="btn btn-success"><?=$this->t('Save') ?></button>
      <a class="btn btn-warning" href="<?=$this->url('change-password') ?>"><?=$this->t('Change Password') ?></a>
		</div>
	</div>
</form>
