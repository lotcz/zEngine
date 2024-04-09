<?php

require_once __DIR__ . '/../classes/forms.php';
require_once __DIR__ . '/../models/form_protection_token.m.php';

/**
* Module that simplifies rendering and processing of forms.
*/
class formsModule extends zModule {

	public $depends_on = ['db', 'messages', 'resources'];
	public $also_install = ['auth'];

	private $protection_enabled = false;
	private $protection_token_min_delay = 3;
	private $protection_token_expires = 60*60;

	public function onEnabled() {
		$this->protection_enabled = $this->getConfigValue('protection_enabled', $this->protection_enabled);
		$this->protection_token_min_delay = $this->getConfigValue('protection_token_min_delay', $this->protection_token_min_delay);
		$this->protection_token_expires = $this->getConfigValue('protection_token_expires', $this->protection_token_expires);
		$this->z->core->includeJS('resources/forms.js', 'bottom');
		$this->z->core->includeCSS('resources/forms.css', 'head');
		$this->z->core->includeJS('resources/forms.js', 'admin.bottom');
		$this->z->core->includeCSS('resources/forms.css', 'admin.head');
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

	public function validateForm($form, $data) {
		$is_valid = true;
		foreach ($form->fields as $field) {
			if (isset($data[$field->name])) {
				$is_valid = $is_valid && $this->validateField($field, $data[$field->name]);
			}
		}
		return $is_valid;
	}

	public function fieldValidation($validation_type, $value, $param = null) {
		$method = 'zForm::validate_' . $validation_type;
		return $method($value, $param);
	}

	public function validateField($field, $value) {
		$is_valid = true;
		if (isset($field->validations) && count($field->validations) > 0) {
			foreach ($field->validations as $validation) {
				$validation_param = null;
				if (isset($validation['param'])) {
					$validation_param = $validation['param'];
				}
				if (!$this->fieldValidation($validation['type'], $value, $validation_param)) {
					$this->z->messages->error($this->z->core->t('Value of field %s is not valid: %s', $field->name, $this->getValidationMessage($validation)));
					$is_valid = false;
				}
			}
		}
		return $is_valid;
	}

	/*
		Create and save protection token and then return the hash to be stored in the form.
	 */
	public function createProtectionTokenHash($form_name) {
		$ip = z::getClientIP();

		$user_session_id = null;
		if ($this->z->isModuleEnabled('auth') && $this->z->auth->isAuth()) {
			$user_session_id = $this->z->auth->session->ival('user_session_id');
		}

		$token_value = z::generateRandomToken(50);
		$token_hash = z::createHash($token_value);
		$token = FormProtectionTokenModel::createToken($this->z->db, $user_session_id, $ip, $form_name, $token_hash);
		return sprintf('%d-%s', $token->ival('form_protection_token_id'), $token_value);
	}

	/**
	 * Verify if token hash is valid and if it is, then delete it
	 * @param  [type] $form_name       [description]
	 * @param  [type] $token_raw_value [description]
	 * @return bool True if token is valid.
	 */
	public function verifyProtectionTokenHash($form_name, $token_raw_value) {
		$ip = z::getClientIP();

		$user_session_id = null;
		if ($this->z->isModuleEnabled('auth') && $this->z->auth->isAuth()) {
			$user_session_id = $this->z->auth->session->ival('user_session_id');
		}

		$arr = explode('-', $token_raw_value);
		if (count($arr) != 2) {
			return false;
		}

		$token_id = intval($arr[0]);
		$token_value = $arr[1];

		$token = new FormProtectionTokenModel($this->z->db, $token_id);
		if (!$token->is_loaded) {
			return false;
		}

		$token_created = $token->dtval('form_protection_token_created');
		$valid_from = $token_created + $this->protection_token_min_delay;
		$valid_to = $token_created + $this->protection_token_expires;

		$token_time_ok = ($valid_from < time()) && ($valid_to > time());

		$session_ok = ($user_session_id == null) || ($token->ival('form_protection_token_user_session_id') == $user_session_id);

		$token_attrs_ok = ($token->val('form_protection_token_ip') == $ip) && ($token->val('form_protection_token_form_name') == $form_name);

		$token_hash_ok = z::verifyHash($token_value, $token->val('form_protection_token_hash'));

		$token_verified = $token_time_ok && $session_ok && $token_attrs_ok && $token_hash_ok;

		if ($token_verified) {
			$token->delete();
		}

		return $token_verified;
	}

	public function processForm($form, $model_class_name) {
		$model = new $model_class_name($this->z->db);

		// DEFAULT VALUES
		foreach ($form->fields as $field) {
			if (isset($field->value)) {
				$model->set($field->name, $field->value);
			}
		}

		$form->z = $this->z;

		if (z::isPost()) {
			if ($form->processInput($_POST)) {
				$form_protection_ok = true;

				if ($this->protection_enabled || $form->protection_enabled) {
					$form_protection_ok = $this->verifyProtectionTokenHash($form->id, z::get('form_token'));
				}

				if ($form_protection_ok) {
					//VALIDATION
					if ($this->validateForm($form, $form->processed_input)) {
						$entity_id_value = z::getInt($model_class_name::getIdName());
						if ($entity_id_value > 0) {
							$model->loadById($entity_id_value);
						}
						$oldModel = $model->clone();
						$model->setData($form->processed_input);
						if ($form->onBeforeUpdate !== null) {
							$onBeforeUpdate = $form->onBeforeUpdate;
							$onBeforeUpdate($this->z, $form, $model, $oldModel);
						}
						try {
							$model->save();
							foreach ($form->fields as $field) {
								if ($field->type == 'multiselect') {
									$model->updateMultiReference($field->multi_ref_table, $field->multi_fk_id_field, $field->multi_fk_other_id_field, $field->selected_items);
								}
							}
							if ($form->onAfterUpdate !== null) {
								$onAfterUpdate = $form->onAfterUpdate;
								$onAfterUpdate($this->z, $form, $model, $oldModel);
							}
							if ($form->suppress_return) {
								$this->z->messages->success($this->z->i18n->translate('Form was successfully saved.'));
							} else {
								$this->z->core->redirectBack($form->ret);
							}
						} catch (Exception $e) {
							$this->z->messages->error($e->getMessage());
						}
					} else {
						$this->z->messages->error('Some field values in the form are not valid! Form cannot be saved.');
						$model->setData($form->processed_input);
					}
				} else {
					if ($this->z->isModuleEnabled('security')) {
						$this->z->security->saveFailedAttempt();
					}
					$this->z->messages->error('Timeout! Cannot process form. Please try to submit form again. You can also try to refresh the page.');
					$model->setData($form->processed_input);
				}

			} else {
				$this->z->messages->error('Input does not validate.');
				$model->setData($form->processed_input);
			}

		} elseif ($this->pathAction() == 'edit') {
			$model->loadById($this->pathParam());
		} elseif ($this->pathAction() == 'delete') {
			$model_id = z::parseInt($this->pathParam());
			if ($form->onBeforeDelete !== null) {
				$onBeforeDelete = $form->onBeforeDelete;
				$onBeforeDelete($this->z, $form, $model_id);
			}
			if ($model->delete($model_id)) {
				if ($form->onAfterDelete !== null) {
					$onAfterDelete = $form->onAfterDelete;
					$onAfterDelete($this->z, $form, $model_id);
				}
				$this->z->core->redirectBack($form->ret);
			}
		}

		$form->prepare($this->z->db, $model);

		// add protection token
		if ($this->protection_enabled || $form->protection_enabled) {
			$form->addField([
				'name' => 'form_token',
				'type' => 'hidden',
				'value' => $this->createProtectionTokenHash($form->id)
			]);
		}

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
					return $this->z->core->t('This field is required. Cannot be left empty.');
				}
			break;
			case 'maxlen' :
				return $this->z->core->t('Maximum length is %s characters.', $param);
			break;
			case 'email' :
				return $this->z->core->t('E-mail address is not in correct form! Please enter valid e-mail address.');
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
			<select name="<?=$name ?>" class="form-select">
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

	public function renderMultiSelect($name, $items, $selected_items, $select_id_field, $select_label_field) {
		?>
			<select name="<?=$name ?>[]" class="form-control chosen" multiple="multiple">
				<?php
					for ($i = 0, $max = count($items); $i < $max; $i++) {
						$id = $items[$i]->ival($select_id_field);
						$selected = '';
						if (in_array($id, $selected_items)) {
							$selected = 'selected';
						}
						?>
							<option value="<?=$id ?>" <?=$selected ?> ><?=$items[$i]->val($select_label_field) ?></option>
						<?php
					}
				?>
			</select>
		<?php
	}

	public function renderForeignKeyLink($name, $link_table, $link_template, $link_id_field, $link_label_field, $value) {
		$result = $this->z->db->executeSelectQuery($link_table, $columns = [$link_label_field], sprintf('%s = ?', $link_id_field), null, 1, [$value], [PDO::PARAM_INT]);
		if ($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$label = $row[$link_label_field];
			$this->z->core->renderLink(
				sprintf($link_template, $value),
				$label
			);
		}
		$result->closeCursor();
	}

	public function renderAliasLink($name, $value) {
		$alias = new AliasModel($this->z->db, $value);
		if ($alias->is_loaded) {
			$url = $alias->val('alias_url');
			$this->z->core->renderLink($url, $url);
		}
		?>
			<input type="hidden" name="<?=$name?>" value="<?=$value ?>" />
		<?php
	}

	public function renderForm($form) {
		$label_css = '';
		$value_css = '';

		if ($form->type === 'horizontal') {
			$label_css = 'col-sm-4';
			$value_css = 'col-sm-8';
		}

		if ($form->render_wrapper) {
			$form->renderStartTag();
		}

		if ($this->z->core->return_path) {
			?>
				<input type="hidden" id="return_url" name="r" value="<?=$this->z->core->return_path ?>" />
				<input type="hidden" id="suppress_return" name="suppress_return" value="false" />
			<?php
		}

		foreach ($form->fields as $field) {
			$disabled = (isset($field->disabled)) ? $field->disabled : '';
			$required = (isset($field->required) && ($field->required)) ? 'required' : '';

			if ($field->type == 'hidden') {
				?>
					<input type="hidden" name="<?=$field->name ?>" id="field_<?=$field->name ?>" value="<?=$field->value ?>" />
				<?php
			} elseif (z::startsWith($field->type, 'static')) {
				switch ($field->type) {
					case 'staticdate' :
					case 'static_date' :
						$render_value = ($field->value === null) ? '' : $this->z->i18n->formatDatetime(strtotime($field->value));
					break;
					case 'staticlocalized' :
					case 'static_localized' :
						$render_value = $this->z->core->t($field->value);
					break;
					case 'static_link':
					case 'staticlink':
						$render_value = $this->z->core->getLink($field->value, $field->value);
					break;
					case 'static_custom' :
						$fn = $field->custom_function;
						$render_value = $fn($field->value);
					break;
					default:
						$render_value = $this->z->core->xssafe($field->value);
				}
				?>
					<div id="<?=$field->name ?>_form_group" class="form-group">
						<label for="<?=$field->name ?>" class="<?=$label_css ?> form-label"><?=$this->z->core->t($field->label) ?>:</label>
						<span><?=$render_value ?></span>
					</div>
				<?php
			} elseif ($field->type == 'bool' || $field->type == 'checkbox' || $field->type == 'boolean') {
				?>
					<div id="<?=$field->name ?>_form_group" class="form-group">
						<div class="form-check">
							<input type="checkbox" id="<?=$field->name ?>" name="<?=$field->name ?>" <?=$disabled ?> value="1" <?=($field->value) ? 'checked' : '' ?> class="form-check-input" />
							<label for="<?=$field->name ?>" class="<?=$label_css ?> form-check-label"><?=$this->z->core->t($field->label) ?></label>
						</div>

						<?php
							if (isset($field->hint)) {
								?>
									<small class="text-muted"><?=$this->z->core->t($field->hint) ?></small>
								<?php
							}
						?>
					</div>
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
					<div class="d-flex flex-row align-items-center">
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
										<input class="<?=isset($button['css']) ? $button['css'] : '' ?> form-button" value="<?=$this->z->core->t($button['label']) ?>" type="<?=$button['type'] ?>" <?=isset($button['onclick']) ? 'onclick="javascript:' . $button['onclick'] . '"' : ''; ?> />
									<?php
								}
							}
						?>
					</div>
				<?php

				if (isset($field->hint)) {
					?>
						<small class="text-muted"><?=$this->z->core->t($field->hint) ?></small>
					<?php
				}

			} elseif ($field->type == 'opening_hours') {
				$this->z->openinghours->renderFormField($field);
			} else {
				?>
					<div id="<?=$field->name ?>_form_group" class="form-group <?=($form->type) == 'horizontal' ? 'row me-2' : '' ?> <?=isset($field->css) ? $field->css : '' ?>">
						<?php
							if (strlen($field->label) > 0) {
								?>
									<label for="<?=$field->name ?>" class="<?=$label_css ?> control-label form-label <?=$form->type === 'inline' ? 'me-2' : '' ?>"><?=$this->z->core->t($field->label) ?>:</label>
								<?php
							}
						?>

						<div class="<?=$value_css ?> <?=$form->type === 'inline' ? 'me-2' : '' ?>">
							<div class="input-group form-field">
								<?php

									switch ($field->type) {

										case 'text' :
										case 'integer' :
										?>
											<input type="text" id="<?=$field->name ?>" name="<?=$field->name ?>" <?=$disabled ?> <?=$required ?> value="<?=$field->value ?>" class="form-control" />
										<?php
										break;

										case 'textarea' :
										?>
											<textarea id="<?=$field->name ?>" name="<?=$field->name ?>" <?=$disabled ?> class="form-control"><?=$field->value ?></textarea>
										<?php
										break;

										case 'wysiwyg' :
										case 'tinymce' :
										?>
											<textarea id="<?=$field->name ?>" name="<?=$field->name ?>" <?=$disabled ?> class="wysiwyg tinymce"><?=$field->value ?></textarea>
										<?php
										break;

										case 'password' :
										?>
											<input type="password" id="<?=$field->name ?>" name="<?=$field->name ?>" <?=$disabled ?> value="<?=$field->value ?>" class="form-control" />
										<?php
										break;

										case 'date' :
										?>
											<input type="datetime-local" id="<?=$field->name ?>" name="<?=$field->name ?>" <?=$disabled ?> value="<?=z::formatDateForHtml(z::phpDatetime($field->value)) ?>" class="form-control" />
										<?php
										break;

										case 'file' :
											?>
												<span><?=$field->value?></span>
												<input type="hidden" name="<?=$field->name ?>" id="field_<?=$field->name ?>" value="<?=$field->value ?>" />
												<input type="file" id="<?=$field->name ?>" name="<?=$field->name ?>_file_input" <?=$disabled ?> class="form-control-file" />
											<?php
										break;

										case 'image' :
											if (isset($field->value)) {
												$this->z->images->renderImage($field->value, isset($field->image_size) ? $field->image_size : 'thumb');
											}
										?>
											<input type="hidden" name="<?=$field->name ?>" id="field_<?=$field->name ?>" value="<?=$field->value ?>" />
											<input type="file" name="<?=$field->name ?>_image_file" <?=$disabled ?> class="form-control-file" accept=".gif,.jpg,.jpeg,.png,.webp" />
										<?php
										break;

										case 'gallery' :
											if (z::parseInt($field->value) > 0) {
												$this->z->gallery->renderGalleryForm($field->value);
												?>
													<input type="hidden" name="<?=$field->name ?>" id="<?=$field->name ?>" value="<?=$field->value ?>" />
												<?php
											} else {
												?>
													<span><?=$this->z->core->t('You must save the form first before adding to gallery.')?></span>
												<?php
											}
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

										case 'multiselect' :
											$this->renderMultiSelect(
												$field->name,
												$field->select_data,
												$field->selected_items,
												$field->select_id_field,
												$field->select_label_field
											);
										break;

										case 'foreign_key_link' :
											$this->renderForeignKeyLink(
												$field->name,
												$field->link_table,
												$field->link_template,
												$field->link_id_field,
												$field->link_label_field,
												$field->value
											);
										break;

										case 'alias_link' :
											$this->renderAliasLink($field->name, $field->value);
											break;

										case 'link' :
											?>
												<p class="form-control-static">
													<?php
														if ($field->link_url != null && $field->link_label != null) {
															$this->z->core->renderLink(
																$field->link_url,
																$field->link_label
															);
														}
													?>
												</p>
											<?php
										break;

									}

									if (isset($field->required) && $field->required) {
										?>
											<div class="input-group-append" data-toggle="tooltip" data-placement="top" title="<?=$this->z->core->t('This field is required. Cannot be left empty.') ?>">
												 <span class="input-group-text">*</span>
											</div>
										<?php
									}

								?>

							</div>

							<?php

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
				function validateForm_<?=$form->id ?>(e, noret) {
					e.preventDefault();
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
					frm.submit(noret);
				}
			</script>

		<?php
	}

}
