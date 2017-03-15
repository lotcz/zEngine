<?php

class zForm {
	
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
	
	function __construct($id = 'entity_name', $action = '', $method = 'POST', $css = 'form-horizontal admin-form') {
		$this->id = $id;
		$this->action = $action;		
		$this->method = $method;
		$this->css = $css;
		$this->ret = get('r', false);
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
		
		foreach ($this->fields as $field) {
			if (isset($field->name)) {
				$field->value = $this->data->val($field->name);
				
				if (($field->type == 'select') && (!isset($field->select_data))) {
					
					// prepare select filter
					
					$field->select_data = zModel::select(
						$db, 
						$field->select_table, /* table */
						null, /* where */
						null, /* bindings */
						null, /* types */
						null, /* paging */
						$field->select_label_field /* orderby */
					);
				} elseif ($field->type == 'foreign_key_link') {
					$entity = new zModel($db);
					$entity->table_name = $field->link_table;
					$filter = sprintf('%s = ?', $field->link_id_field);
					$entity->loadSingleFiltered($filter, [$this->data->val($field->name)]);
					$field->link_label = $entity->val($field->link_label_field);
					$field->link_url = sprintf($field->link_template, $entity->val($field->link_id_field));
				}
			}
		}
	}
	
	public function renderStartTag() {
		?>
			<form id="form_<?=$this->id ?>" action="<?=$this->action ?>" method="<?=$this->method ?>" class="<?=$this->css ?>" enctype="multipart/form-data">
		<?php
	}
			
}