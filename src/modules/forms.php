<?php

require_once __DIR__ . '/../classes/forms.php';

class formsModule extends zModule {
	
	public function onEnabled() {
		$this->requireModule('mysql');
		$this->requireModule('messages');
	}
	
	public function pathParam() {
		return $this->z->core->getPath(-1);
	}
	
	public function pathAction() {
		return $this->z->core->getPath(-2);
	}
	
	public function processForm($form, $model_class_name) {
		$model = new $model_class_name($this->z->core->db);		
		if (isPost()) {		
			if ($form->processInput($_POST)) {
				if (parseInt($_POST[$model->id_name]) > 0) {
					$model->loadById($_POST[$model->id_name]);			
				}
				$model->setData($form->processed_input);
				if ($model->save()) {
					if ($form->ret) {
						$this->z->core->redirect($form->ret);
					}
				}
			} else {
				$this->z->messages->error('Input does not validate.');
				$model->setData($form->processed_input);
			}
		} elseif ($this->pathAction() == 'edit') {		
			$model->loadById($this->pathParam());
			$this->z->core->setPageTitle($this->z->core->t($form->entity_title) . ': ' . $this->z->core->t('Editing'));
		} elseif ($this->pathAction() == 'delete') {
			if ($model->deleteById($this->pathParam())) {
				if ($form->ret) {
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
				if (parseInt($param) > 1) {
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
	
	public function renderSelect($name, $items, $id_name, $label_name, $selected_value = null) {
		?>
			<select name="<?=$name ?>" class="form-control">
				<?php
					for ($i = 0, $max = count($items); $i < $max; $i++) {
						$value = $items[$i]->val($id_name);
						$selected = '';
						if ($value == $selected_value) {
							$selected = 'selected';
						}
						?>
							<option value="<?=$items[$i]->val($id_name) ?>" <?=$selected ?> ><?=$items[$i]->val($label_name) ?></option>
						<?php
					}
				?>
			</select>
		<?php		
	}
	
	public function renderForm($form) {
		
		$this->z->core->includeJS('resources/forms.js');
		
		if ($form->render_wrapper) {
			$form->renderStartTag();
		}
		
		if ($form->ret) {
			?>
				<input type="hidden" name="r" value="<?=$form->ret ?>" />
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
					<div class="form-group">
						<label for="<?=$field->name ?>" class="col-sm-4 control-label"><?=$this->z->core->t($field->label) ?>:</label>
						<div class="col-sm-8">
							<?php
														
								switch ($field->type) {									
									
									case 'text' :
									?>
										<input type="text" name="<?=$field->name ?>" <?=$disabled ?> value="<?=$field->value ?>" class="form-control" />
									<?php
									break;			
									
									case 'password' :
									?>
										<input type="password" name="<?=$field->name ?>" <?=$disabled ?> value="<?=$field->value ?>" class="form-control" />
									<?php
									break;	
									
									case 'bool' :
									?>
										<input type="checkbox" name="<?=$field->name ?>" <?=$disabled ?> value="1" <?=($field->value) ? 'checked' : '' ?> class="form-control form-control-checkbox" />
									<?php
									break;	
									
									case 'date' :
									?>
										<input type="datetime" name="<?=$field->name ?>" <?=$disabled ?> value="<?=$field->value ?>" class="form-control" />
									<?php
									break;
									
									case 'file' :
									?>										
										<input type="file" name="<?=$field->name ?>" <?=$disabled ?> class="form-control-file" />
									<?php
									break;
									
									case 'image' :
										global $images;
										$images->renderImage($field->value, 'mini-thumb');
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