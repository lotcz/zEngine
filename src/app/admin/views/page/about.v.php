<table>
	<tr>
		<td><?=$this->t('Application version')?>:</td>
		<td><strong><?=$this->z->app->version ?></strong></td>
	</tr>
	<tr>
		<td><?=$this->t('zEngine version')?>:</td>
		<td><strong><?=$this->z->version ?></strong> (<?=$this->t('required at least %s', $this->z->app->require_z_version) ?>)</td>
	</tr>
