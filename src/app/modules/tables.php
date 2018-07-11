<?php

require_once __DIR__ . '/../classes/paging.php';
require_once __DIR__ . '/../classes/tables.php';

/**
* Module that handles rendering of paged html tables.
*/
class tablesModule extends zModule {

	public function onEnabled() {
		$this->requireModule('mysql');
	}

	public function renderTable($table) {
		if (isset($table->paging)) {
			$table->paging->renderLinks();
		}

		?>

			<div class="table-responsive">
				<table class="table <?=$table->css ?>">
					<thead>
						<tr>
							<?php
								foreach ($table->fields as $field) {
									?>
										<th><?=$this->z->core->t($field->label) ?></th>
									<?php
								}
							?>
							<th></th>
						</tr>
					</thead>
					<tbody>
					<?php

						foreach ($table->data as $row) {
							$item_url = $this->z->core->url(sprintf($table->edit_link, $row->val($table->id_field)), $this->z->core->raw_path);
							?>
								<tr onclick="javascript:document.location = '<?=$item_url ?>';">
									<?php
										foreach ($table->fields as $field) {
											?>
												<td>
													<?php
														if (!isset($field->type)) {
															echo $row->val($field->name);
														} elseif ($field->type == 'date') {
															echo $this->z->i18n->formatDate($row->dtval($field->name));
														} elseif ($field->type == 'datetime') {
															echo $this->z->i18n->formatDatetime($row->dtval($field->name));
														} elseif ($field->type == 'localized') {
															echo $this->z->core->t($row->val($field->name));
														}
													?>
												</td>
											<?php
										}
									?>
									<td><a href="<?=$item_url ?>"><?=$this->z->core->t('Edit') ?></a></td>
								</tr>
							<?php
						}
					?>
					</tbody>
				</table>
			</div>

		<?php
	}

}
