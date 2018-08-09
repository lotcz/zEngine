<?php

require_once __DIR__ . '/../classes/paging.php';
require_once __DIR__ . '/../classes/tables.php';

/**
* Module that handles rendering of paged html tables.
*/
class tablesModule extends zModule {

	public function onEnabled() {
		$this->requireModule('db');
	}

	public function renderTable($table) {
		if (isset($table->paging)) {
			$table->paging->renderLinks();
		}

		if (sizeof($table->data) == 0) {
			?>
				<div class="p-2"><?=$this->z->core->t($table->no_data_message) ?></div>
			<?php
		} else {
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
							</tr>
						</thead>

						<tbody>
							<?php

								foreach ($table->data as $row) {
									$item_url = $this->z->core->url(sprintf($table->edit_link, $row->val($table->id_field)), $this->z->core->raw_path);
									?>
										<tr onclick="javascript:document.location = '<?=$item_url ?>';" class="">
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
																} elseif ($field->type == 'custom') {
																	$fn = $field->custom_function;
																	echo $fn($row->val($field->name));
																}
															?>
														</td>
													<?php
												}
											?>
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
}
