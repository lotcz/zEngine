<?php

/**
* This class simplifies generation of html forms.
*/
class zForm {

	public $id;
	public $type = 'horizontal';
	public $action;
	public $method;
	public $css;
	public $entity_title;
	public $fields = [];
	public $data = [];
	public $processed_input = [];
	public $is_valid = true;
	public $render_wrapper = false;
	public $images_module = null;

	public $onBeforeUpdate = null;
	public $onAfterUpdate = null;
	public $onBeforeDelete = null;
	public $onAfterDelete = null;

	function __construct($id = 'entity_name', $action = '', $method = 'POST', $css = 'form-horizontal admin-form') {
		$this->id = $id;
		$this->action = $action;
		$this->method = $method;
		$this->css = $css;
	}

	public function addField($field) {
		$objField = (object)$field;
		$objField->value = isset($objField->value) ? $objField->value : null;
		$objField->select_label_localized = isset($objField->select_label_localized) ? $objField->select_label_localized : false;
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
					if (!isset($this->images_module)){
						throw new Exception('Images module is not enabled, cannot upload image!');
					}
					$name = $field->name . '_image_file';
					if (isset($_FILES[$name]) && strlen($_FILES[$name]['name'])) {
						$image = $this->images_module->uploadImage($name);
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

					if (isset($field->empty_option_name) && strlen($field->empty_option_name) > 0) {
						$empty_option = new	zModel();
						$empty_option->set($field->select_id_field, null);
						$empty_option->set($field->select_label_field, $field->empty_option_name);
						$field->select_data[] = $empty_option;
					}
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
	
	/*
		VALIDATIONS
	*/
		
	static function validate_length($value, $param) {
		return (isset($value) && (strlen($value) >= z::parseInt($param)));
	}

	static function validate_maxlen($value, $param) {
		return (strlen($value) <= z::parseInt($param));
	}

	static function validate_match($value, $param) {
		return ($value == $param);
	}

	static function validate_password($value, $param) {
		return Self::validate_length($value, $param);
	}

	static function validate_ip($value) {
		return Self::validate_length($value, 5);
	}

	static function validate_date($value, $param) {
		return Self::validate_length($value, 5);
	}

	static function validate_html($value, $param) {
		return true;
	}

	static function validate_email($value) {
		return filter_var($value, FILTER_VALIDATE_EMAIL);
	}

	// integer - param => allow empty
	static function validate_integer($value, $param) {		
		return ($param && ($value == '')) || (is_int($value) || ctype_digit($value));
	}

	static function validate_min($value, $param) {
		return (z::parseFloat($value) >= z::parseFloat($param));
	}

	static function validate_max($value, $param) {
		return (z::parseFloat($value) <= z::parseFloat($param));
	}
	
	static function validate_decimal($value) {
		return is_numeric($value);
	}

	static function validate_price($value) {
		return Self::validate_decimal($value);
	}

	static function validate_name($value) {
		return Self::validate_length($value, 1);
	}

	static function validate_zip($value) {
		return Self::validate_length($value, 4) 
			&& Self::validate_integer($value, false)
			&& Self::validate_min($value, 10000)
			&& Self::validate_max($max, 99999);
	}

}