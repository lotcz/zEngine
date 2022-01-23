<?php

require_once __DIR__ . '/../classes/paging.php';
require_once __DIR__ . '/../classes/table.php';

/**
* Module that handles rendering of paged html tables.
*/
class tablesModule extends zModule {

	public $depends_on = ['db', 'resources'];

	public function onEnabled() {
		$this->z->core->includeCSS('resources/tables.css');
		$this->z->core->includeCSS('resources/tables.css', false, 'admin.head');
	}

	public function createPaging() : zPaging {
		return new zPaging(0, $this->getConfigValue('page_size'), $this->getConfigValue('max_pages_links'));
	}

	public function createTable($entity_name = 'entity name', $view_name = null, $sort_fields = [], $default_sort = null, $css = '') : zTable {
		$table = new zTable($entity_name, $view_name, $css);
		$default_paging = $this->createPaging();
		$default_paging->allowed_sorting_items = $sort_fields;
		if ($default_sort) {
			$sorting_arr = explode(' ', $default_sort);
			$default_paging->active_sorting = $sorting_arr[0];
			if (count($sorting_arr) > 1) {
				$default_paging->sorting_desc = ($sorting_arr[1] == 'desc');
			}
		}
		$table->paging = zPaging::getFromUrl($default_paging);
		$table->sort_fields = $sort_fields;
		$table->detail_page = str_replace('_', '-', $entity_name);
		return $table;
	}

	public function prepareTable($table) {
		// filtering
		if (isset($table->filter_form) && $table->filter_form->is_valid) {
			$filter_values = $table->filter_form->processed_input;
			$where = [];
			$table->bindings = [];
			$table->types = [];
			foreach ($table->filter_form->fields as $field) {
				if ($field->type == 'text') {
					foreach ($field->filter_fields as $filter_field) {
						$field->value = $filter_values[$field->name];
						if (strlen($filter_values[$field->name]) > 0) {
							$where[] = sprintf('%s like ?', $filter_field);
							$table->bindings[] = '%' . $filter_values[$field->name] . '%';
							$table->types[] = PDO::PARAM_STR;
						}
					}
				}
			}
			if (count($where) > 0) {
				$table->where = implode($where, ' or ');
			} else {
				$table->where = null;
				$table->bindings = null;
				$table->types = null;
			}
		}

		$table->paging->total_records = $this->z->db->getRecordCount(
			$table->view_name,
			$table->where,
			$table->bindings,
			$table->types
		);

		$table->data = zModel::select(
			$this->z->db,
			$table->view_name,
			$table->where,
			$table->paging->getOrderBy(),
			$table->paging->getLimit(),
			$table->bindings,
			$table->types
		);

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
											<th>
												<?php
													if (in_array($field->name, $table->sort_fields)) {
														if ($field->name == $table->paging->active_sorting) {
															$sort_url = $table->paging->getLinkUrl(null, null, $field->name, !$table->paging->sorting_desc, null);
														} else {
															$sort_url = $table->paging->getLinkUrl(0, null, $field->name, false, null);
														}
														?>
															<a href="<?=$sort_url?>">
																<?php
																	echo $this->z->core->t($field->label);
																	if ($field->name == $table->paging->active_sorting) {
																		?>
																			<span class="caret <?=$table->paging->sorting_desc ? '' : 'caret-up' ?>"/>
																		<?php
																	}
																?>
															</a>
														<?php
													} else {
														echo $this->z->core->t($field->label);
													}
												?>
											</th>
										<?php
									}
								?>
							</tr>
						</thead>

						<tbody>
							<?php
								foreach ($table->data as $row) {
									$item_url = $this->z->core->url(sprintf($table->edit_link, $row->val($table->id_field_name)), $this->z->core->raw_path);
									?>
										<tr onclick="javascript:document.location = '<?=$item_url ?>';">
											<?php
												foreach ($table->fields as $field) {
													?>
														<td>
															<?php
																if (!isset($field->type)) {
																	echo $this->z->core->xssafe($row->val($field->name));
																} elseif ($field->type == 'date') {
																	echo $this->z->core->formatDate($row->dtval($field->name));
																} elseif ($field->type == 'datetime') {
																	echo $this->z->core->formatDatetime($row->dtval($field->name));
																} elseif ($field->type == 'localized') {
																	echo $this->z->core->t($row->val($field->name));
																} elseif ($field->type == 'integer') {
																	echo $row->ival($field->name);
																} elseif ($field->type == 'bool') {
																	echo $this->z->core->t(($row->ival($field->name) == 1) ? 'Yes' : 'No');
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
