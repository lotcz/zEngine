<form id="import_form" method="POST">
	<div class="form-group row">
		<label for="import_addresses" class="col-sm-2 col-form-label"><?=$this->t('Addreses') ?></label>
		<div class="col-sm-10">
			<textarea id="import_addresses" name="import_addresses" aria-describedby="help" class="form-control" ></textarea>
			<small id="help" class="form-text text-muted"><?=$this->t('Enter email addresses separated by space, comma or new line.') ?></small>
		</div>
	</div>
	<div class="form-group row">
		<div class="col-sm-10 offset-sm-2">
			<input type="submit" class="btn btn-success" value="<?=$this->t('Import') ?>">
		</div>
	</div>
</form>
