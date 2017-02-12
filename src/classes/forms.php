<?php

class Form {
	
	public $id;
	public $action;	
	public $method;
	public $css;
	public $entity_title;
	public $ret = false;
	public $fields = [];
	public $data = [];	
	public $processed_input = [];
	public $is_valid = true;
	public $render_wrapper = false;
	
	// admin form
	public $is_admin_form = false;
	public $del_url;
	
	function __construct($id = 'entity_name', $action = '', $method = 'POST', $css = 'form-horizontal admin-form') {
		$this->id = $id;
		$this->action = '';//$action;		
		$this->method = $method;
		$this->css = $css;		
		$this->ret = _g('r', false);
	}
	
	public function addField($field) {
		$objField = (object)$field;
		$objField->value = isset($objField->value) ? $objField->value : null;
		if (isset($field['name'])) {
			$this->fields[$field['name']] = $objField;
		} else {
			$this->fields[] = $objField;
		}
	}
	
	public function add($fields) {
		if (is_array($fields)) {			
			foreach ($fields as $field) {
				$this->addField($field);
			}			
		} else {
			$this->addField($fields);
		}
	}
	
	static function submitted($method = 'POST') {
		return ($_SERVER['REQUEST_METHOD'] == $method);
	}
	
	public function processInput($data) {
		$result = [];
		$is_valid = true;
		foreach ($this->fields as $field) {
			if (isset($field->name)) {
				if ($field->type == 'bool') {
					$result[$field->name] = isset($data[$field->name]) ? 1 : 0;
				} elseif ($field->type == 'image') {
					/* upload image */
					global $images;
					$name = $field->name . '_image_file';
					if (isset($_FILES[$name]) && strlen($_FILES[$name]['name'])) {
						$image = $images->uploadImage($name);
						if (isset($image) && strlen($image) > 0) {
							
							$result[$field->name] = $image;
						} else {
							$is_valid = false;							
						}
					}				
				} elseif (isset($data[$field->name]) && !isset($field->disabled)) {
					$result[$field->name] = $data[$field->name];
				}
			}
		}
		$this->processed_input = $result;
		return ($is_valid) ? $result : false;
	}
	
	public function prepare($db, $data) {
		$this->data = $data;
		
		if ($this->is_admin_form) {
			$this->action = sprintf($this->action, $this->data->val($this->id));
			$this->del_url = sprintf($this->del_url, $this->data->val($this->id));
		}
		
		foreach ($this->fields as $field) {
			if (isset($field->name)) {
				$field->value = $this->data->val($field->name);
				
				if (($field->type == 'select') && (!isset($field->select_data))) {
					$field->select_data = ModelBase::select(
						$db, 
						$field->select_table, /* table */
						null, /* where */
						null, /* bindings */
						null, /* types */
						null, /* paging */
						$field->select_label_field /* orderby */
					);
				} elseif ($field->type == 'foreign_key_link') {
					$entity = new ModelBase($db);
					$entity->table_name = $field->link_table;
					$filter = sprintf('%s = ?', $field->link_id_field);
					$entity->loadSingleFiltered($filter, [$this->data->val($field->name)]);
					$field->link_label = $entity->val($field->link_label_field);
					$field->link_url = sprintf($field->link_template, $entity->val($field->link_id_field));
				}
			}
		}
	}
	
	static function getValidationMessage($validation) {
		$type = $validation['type'];
		$param = '';
		if (isset($validation['param'])) {
			$param = $validation['param'];
		}
		switch ($type) {
			case 'confirm' :
				return t('Passwords don\'t match.', $param);
			break;
			case 'min' :
				return t('Value must be higher than %s.', $param);
			break;
			case 'length' :
				if (parseInt($param) > 1) {
					return t('Value must be at least %s characters long.', $param);
				} else {
					return t('This field cannot be empty.');
				}
			break;
			case 'maxlen' :				
				return t('Maximum length is %s characters.', $param);				
			break;
			case 'email' :
				return t('Please enter valid e-mail address.');
			break;
			case 'date' :
				return t('Please enter valid date.');
			break;
			case 'ip' :
				return t('Please enter valid IP address.');
			break;
			case 'integer' :
				return t('Please enter whole number.');
			break;
			case 'decimal' :
			case 'price' :
				return t('Please enter valid decimal number.');
			break;
			default:				
				return t('Required.');		
		}
	}
	
	public function renderStartTag() {
		?>
			<form id="form_<?=$this->id ?>" action="<?=$this->action ?>" method="<?=$this->method ?>" class="<?=$this->css ?>" enctype="multipart/form-data">
		<?php
	}
	
	public function render() {
		global $base_url;
		
		if ($this->render_wrapper) {
			$this->renderStartTag();
		}
								
		if ($this->ret) {
			?>
				<input type="hidden" name="r" value="<?=$this->ret ?>" />
			<?php
		}
		
		foreach ($this->fields as $field) {
			$disabled = (isset($field->disabled)) ? $field->disabled : '';
			
			if ($field->type == 'hidden') {
				?>
					<input type="hidden" name="<?=$field->name ?>" id="field_<?=$field->name ?>" value="<?=$field->value ?>" />
				<?php
			} elseif ($field->type == 'begin_group') {
				?>
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><?=t($field->label) ?></h3>
						</div>
						<div class="panel-body">
				<?php
			} elseif ($field->type == 'end_group') {
				?>
						</div>
					</div>
				<?php					
			} else {
				?>
					<div class="form-group">
						<label for="<?=$field->name ?>" class="col-sm-4 control-label"><?=t($field->label) ?>:</label>
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
										renderSelect(
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
													renderLink(
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
											<div class="form-validation" id="<?=$field->name ?>_validation_<?=$validation['type'] ?>"><?= isset($validation['message']) ? $validation['message'] : Form::getValidationMessage($validation) ?></div>
										<?php
									}
								}
								
								if (isset($field->hint)) {
									?>
										<small class="text-muted"><?=t($field->hint) ?></small>
									<?php
								}
							?>											
							
						</div>
						
					</div>
				<?php
			}						
		}
						
		if ($this->is_admin_form) {
			?>
	
				<div class="form-buttons">
					<a class="form-button" href="<?=_url($this->ret) ?>"><?= t('Back') ?></a>
					<input type="button" onclick="javascript:deleteItem_<?=$this->id ?>();" class="btn btn-danger form-button" value="<?=t('Delete') ?>">
					<input type="button" onclick="javascript:validateForm_<?=$this->id ?>();" class="btn btn-success form-button" value="<?=t('Save') ?>">
				</div>
				
				<script>
					function deleteItem_<?=$this->id ?>() {
						if (confirm('<?= t('Are you sure to delete this item?') ?>')) {
							document.location = '<?= _url($this->del_url, $this->ret) ?>';
						}
					}	
				</script>
			<?php
		}
		
		if ($this->render_wrapper) {
			?>
				</form>
			<?php
		}
		
		?>
				
			<script>
				function validateForm_<?=$this->id ?>() {
					var frm = new formValidation('form_<?=$this->id ?>');
						<?php
							foreach ($this->fields as $field) {
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

class AdminForm extends Form {
		
	function __construct($entity) {
		parent::__construct($entity . '_id', 'admin/' . $entity . '/edit/%d');
		$this->entity_title = ucwords(str_replace('_', ' ', $entity));
		$this->del_url = 'admin/' . $entity . '/delete/%d';
		$this->is_admin_form = true;
		$this->render_wrapper = true;
		$this->addField(
			[
				'name' => $this->id,
				'type' => 'hidden'
			]
		);
	}
	
}