<?php

require_once __DIR__ . '/../classes/forms.php';

class formsModule extends zModule {

	public function onEnabled() {
		$this->requireModule('mysql');
		$this->requireModule('messages');
		$this->z->core->includeJS('resources/forms.js');
	}

	public function pathParam() {
		return $this->z->core->getPath(-1);
	}

	public function pathAction() {
		return $this->z->core->getPath(-2);
	}

	public function get($name, $def = null) {
		return $this->z->core->get($name, $def);
	}

	public function processForm($form, $model_class_name) {
		$model = new $model_class_name($this->z->core->db);
		if (z::isPost()) {
			if ($this->z->moduleEnabled('images')) {
				$form->images_module = $this->z->images;
			}
			if ($form->processInput($_POST)) {
				if (z::parseInt($_POST[$model->id_name]) > 0) {
					$model->loadById($_POST[$model->id_name]);
				}
				$model->setData($form->processed_input);
				if ($form->onBeforeUpdate !== null) {
					$onBeforeUpdate = $form->onBeforeUpdate;
					$onBeforeUpdate($this->z, $form, $model);
				}
				if ($model->save()) {
					if ($form->onAfterUpdate !== null) {
						$onAfterUpdate = $form->onAfterUpdate;
						$onAfterUpdate($this->z, $form, $model);
					}
					$this->z->core->redirectBack();
				}
			} else {
				$this->z->messages->error('Input does not validate.');
				$model->setData($form->processed_input);
			}
		} elseif ($this->pathAction() == 'edit') {
			$model->loadById($this->pathParam());
			$this->z->core->setPageTitle($this->z->core->t($form->entity_title) . ': ' . $this->z->core->t('Editing'));
		} elseif ($this->pathAction() == 'delete') {
			$model_id = z::parseInt($this->pathParam());
			if ($form->onBeforeDelete !== null) {
				$onBeforeDelete = $form->onBeforeDelete;
				$onBeforeDelete($this->z, $form, $model_id);
			}
			if ($model->deleteById($model_id)) {
				if ($form->onAfterDelete !== null) {
					$onAfterDelete = $form->onAfterDelete;
					$onAfterDelete($this->z, $form, $model_id);
				}
				if (isset($form->ret)) {
					$this->z->core->redirect($form->ret);
				}
			}
		} else {
			$this->z->core->setPageTitle($this->z->core->t($form->entity_title) . ': ' . $this->z->core->t('New'));
		}
		$form->prepare($this->z->core->db, $model);
	}

	public function getValidationMessage($validation) {
		$type = $validation['type'];
		$param = '';
		if (isset($validation['param'])) {
			$param = $validation['param'];
		}
		switch ($type) {
			case 'confirm' :
				return $this->z->core->t('Passwords don\'t match.', $param);
			break;
			case 'min' :
				return $this->z->core->t('Value must be higher than %s.', $param);
			break;
			case 'length' :
				if (z::parseInt($param) > 1) {
					return $this->z->core->t('Value must be at least %s characters long.', $param);
				} else {
					return $this->z->core->t('This field cannot be empty.');
				}
			break;
			case 'maxlen' :
				return $this->z->core->t('Maximum length is %s characters.', $param);
			break;
			case 'email' :
				return $this->z->core->t('Please enter valid e-mail address.');
			break;
			case 'date' :
				return $this->z->core->t('Please enter valid date.');
			break;
			case 'ip' :
				return $this->z->core->t('Please enter valid IP address.');
			break;
			case 'integer' :
				return $this->z->core->t('Please enter whole number.');
			break;
			case 'decimal' :
			case 'price' :
				return $this->z->core->t('Please enter valid decimal number.');
			break;
			default:
				return $this->z->core->t('Required.');
		}
	}

	public function renderSelect($name, $items, $id_name, $label_name, $localized = false, $selected_value = null) {
		?>
			<select name="<?=$name ?>" class="form-control">
				<?php
					for ($i = 0, $max = count($items); $i < $max; $i++) {
						$value = $items[$i]->ival($id_name);						
						$selected = '';
						if ($value == $selected_value) {
							$selected = 'selected';
						}
						$label_text = $items[$i]->val($label_name);
						if ($localized) {
							$label_text = $this->z->core->t($items[$i]->val($label_name));
						}
							?>
								<option value="<?=$items[$i]->val($id_name) ?>" <?=$selected ?> ><?=$label_text ?></option>
							<?php
					}
				?>
			</select>
		<?php
	}

	public function renderForm($form) {
		$label_css = '';
		$value_css = '';

		if ($form->type == 'inline') {
			$label_css = '';
			$value_css = '';
		} else {
			$label_css = 'col-sm-4';
			$value_css = 'col-sm-8';
		}

		if ($form->render_wrapper) {
			$form->renderStartTag();
		}

		if ($this->z->core->return_path) {
			?>
				<input type="hidden" name="r" value="<?=$this->z->core->return_path ?>" />
			<?php
		}

		foreach ($form->fields as $field) {
			$disabled = (isset($field->disabled)) ? $field->disabled : '';

			if ($field->type == 'hidden') {
				?>
					<input type="hidden" name="<?=$field->name ?>" id="field_<?=$field->name ?>" value="<?=$field->value ?>" />
				<?php
			} elseif ($field->type == 'begin_group') {
				?>
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><?=$this->z->core->t($field->label) ?></h3>
						</div>
						<div class="panel-body">
				<?php
			} elseif ($field->type == 'end_group') {
				?>
						</div>
					</div>
				<?php
			} elseif ($field->type == 'buttons') {
				?>
					<div class="form-buttons">
						<?php
							foreach ($field->buttons as $button) {
								if ($button['type'] == 'link') {
									$this->z->core->renderLink(
										$button['link_url'],
										$button['label'],
										isset($button['css']) ? $button['css'] : null,
										isset($button['ret']) ? $button['ret'] : null
									);
								} else {
									?>
										<input class="<?=isset($button['css']) ? $button['css'] : '' ?> form-button" value="<?=$button['label'] ?>" type="<?=$button['type'] ?>" <?=isset($button['onclick']) ? 'onclick="javascript:' . $button['onclick'] . '"' : ''; ?> />
									<?php
								}
							}
						?>
					</div>
				<?php
			} else {
				?>
					<div id="<?=$field->name ?>_form_group" class="form-group">
						<label for="<?=$field->name ?>" class="<?=$label_css ?> control-label form-label"><?=$this->z->core->t($field->label) ?>:</label>
						<div class="<?=$value_css ?> form-field">
							<?php

								switch ($field->type) {

									case 'text' :
									?>
										<input type="text" id="<?=$field->name ?>" name="<?=$field->name ?>" <?=$disabled ?> value="<?=$field->value ?>" class="form-control" />
									<?php
									break;

									case 'textarea' :
									?>
										<textarea id="<?=$field->name ?>" name="<?=$field->name ?>" <?=$disabled ?> class="form-control"><?=$field->value ?></textarea>
									<?php
									break;

									case 'html' :
									?>
										<textarea id="<?=$field->name ?>" name="<?=$field->name ?>" <?=$disabled ?> class="htmlarea"><?=$field->value ?></textarea>
									<?php
									break;

									case 'password' :
									?>
										<input type="password" id="<?=$field->name ?>" name="<?=$field->name ?>" <?=$disabled ?> value="<?=$field->value ?>" class="form-control" />
									<?php
									break;

									case 'bool' :
									?>
										<input type="checkbox" id="<?=$field->name ?>" name="<?=$field->name ?>" <?=$disabled ?> value="1" <?=($field->value) ? 'checked' : '' ?> class="form-control form-control-checkbox" />
									<?php
									break;

									case 'date' :
									?>
										<input type="datetime" id="<?=$field->name ?>" name="<?=$field->name ?>" <?=$disabled ?> value="<?=$field->value ?>" class="form-control" />
									<?php
									break;

									case 'file' :
									?>
										<input type="file" id="<?=$field->name ?>" name="<?=$field->name ?>" <?=$disabled ?> class="form-control-file" />
									<?php
									break;

									case 'image' :
										if (isset($field->value)) {
											$this->z->images->renderImage($field->value, 'mini-thumb');
										}
									?>
										<input type="hidden" name="<?=$field->name ?>" id="field_<?=$field->name ?>" value="<?=$field->value ?>" />
										<input type="file" name="<?=$field->name ?>_image_file" <?=$disabled ?> class="form-control-file" />
									<?php
									break;

									case 'select' :
										$this->renderSelect(
											$field->name,
											$field->select_data,
											$field->select_id_field,
											$field->select_label_field,
											$field->select_label_localized,
											$field->value
										);
									break;

									case 'foreign_key_link' :
										?>
											<p class="form-control-static">
												<?php
													$this->z->core->renderLink(
														$field->link_url,
														$field->link_label
													);
												?>
											</p>
										<?php
									break;

									case 'static' :
										?>
											<p class="form-control-static"><?=$field->value ?></p>
										<?php
									break;

									case 'staticdate' :
										?>
											<p class="form-control-static"><?=$this->z->i18n->formatDatetime(strtotime($field->value));?></p>
										<?php
									break;

									case 'staticlocalized' :
										?>
											<p class="form-control-static"><?=$this->z->core->t($field->value);?></p>
										<?php
									break;
								}

								if (isset($field->validations)) {
									foreach ($field->validations as $validation) {
										?>
											<div class="form-validation" id="<?=$field->name ?>_validation_<?=$validation['type'] ?>"><?= isset($validation['message']) ? $validation['message'] : $this->getValidationMessage($validation) ?></div>
										<?php
									}
								}

								if (isset($field->hint)) {
									?>
										<small class="text-muted"><?=$this->z->core->t($field->hint) ?></small>
									<?php
								}
							?>

						</div>

					</div>
				<?php
			}
		}

		if ($form->render_wrapper) {
			?>
				</form>
			<?php
		}

		?>

			<script>
				function validateForm_<?=$form->id ?>() {
					var frm = new formValidation('form_<?=$form->id ?>');
						<?php
							foreach ($form->fields as $field) {
								if (isset($field->validations)) {
									foreach ($field->validations as $val) {
										?>
											frm.add('<?=$field->name ?>', '<?=$val['type'] ?>', '<?=(isset($val['param'])) ? $val['param'] : 1 ?>');
										<?php
									}
								}
							}
						?>
					frm.submit();
				}
			</script>

		<?php
	}

}
