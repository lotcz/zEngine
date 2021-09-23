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
	public $ret;
	public $entity_title;
	public $fields = [];
	public $data = [];
	public $processed_input = [];
	public $is_valid = true;
	public $render_wrapper = false;
	public $images_module = null;
	public $protection_enabled = false;

	public $onBeforeUpdate = null;
	public $onAfterUpdate = null;
	public $onBeforeDelete = null;
	public $onAfterDelete = null;

	function __construct($id = 'entity_name', $action = '', $method = 'POST', $css = '') {
		$this->id = $id;
		$this->action = $action;
		$this->method = $method;
		$this->css = $css;
		$this->ret = z::get('r');
		$this->detail_page = str_replace('_', '-', $id);
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
		$this->is_valid = true;
		foreach ($this->fields as $field) {
			if (isset($field->name) && (!(isset($field->disabled) && $field->disabled)) && (!z::startsWith($field->type, 'static')) && ($field->type !== 'buttons')) {
				switch ($field->type) {
					case 'bool':
						$result[$field->name] = isset($data[$field->name]) ? 1 : 0;
						break;

					case 'integer':
					case 'select':
						if (isset($data[$field->name])) {
							$result[$field->name] = z::parseInt($data[$field->name]);
						} else {
							$this->is_valid = false;
						}
						break;

					case 'alias_link':
					case 'gallery':
						if (isset($data[$field->name])) {
							$result[$field->name] = z::parseInt($data[$field->name]);
						} else {
							$result[$field->name] = null;
						}
						break;

					case 'image': /* upload image */
						if (!isset($this->images_module)) {
							throw new Exception('Images module is not enabled, cannot upload image!');
						}
						$name = $field->name . '_image_file';
						if (isset($_FILES[$name]) && strlen($_FILES[$name]['name'])) {
							$image = $this->images_module->uploadImage($name);
							if (isset($image) && strlen($image) > 0) {
								$result[$field->name] = $image;
							} else {
								$this->is_valid = false;
							}
						}
						break;

					case 'file': /* upload file */
						if (!isset($this->files_module)) {
							throw new Exception('Files module is not enabled, cannot upload file!');
						}
						$name = $field->name . '_file_input';
						if (isset($_FILES[$name]) && strlen($_FILES[$name]['name'])) {
							$file = $this->files_module->uploadFile($name);
							if (isset($file) && strlen($file) > 0) {
								$result[$field->name] = $file;
							} else {
								$this->is_valid = false;
							}
						}
						break;

					case 'date':
						$result[$field->name] = empty($data[$field->name]) ? null : $data[$field->name];
						break;

					case 'opening_hours':
						for ($d = 1; $d <= 7; $d++) {
							$day_name = $this->openinghours_module->getDayName($d);
							$from = $field->prefix . $day_name . '_from';
							$to = $field->prefix . $day_name . '_to';
							$result[$from] = $this->openinghours_module->getTime($data, $from);
							$result[$to] = $this->openinghours_module->getTime($data, $to);
						}
						break;

					case 'multiselect':
						$field->selected_items = [];
						if (!empty($data[$field->name])) {
							$field->selected_items = $data[$field->name];
						}
						break;

					default:
						if (isset($data[$field->name])) {
							$result[$field->name] = $data[$field->name];
						} else {
							$this->is_valid = false;
						}
				}
			}
		}
		$this->processed_input = $result;
		return ($this->is_valid) ? $result : false;
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
						$field->select_label_field,
						null,
						null,
						null /* types */
					);

					if (isset($field->empty_option_name) && strlen($field->empty_option_name) > 0) {
						$empty_option = new	zModel();
						$empty_option->set($field->select_id_field, null);
						$empty_option->set($field->select_label_field, $field->empty_option_name);
						array_splice($field->select_data, 0, 0, [$empty_option]);
					}
				} elseif ($field->type == 'foreign_key_link') {
					$filter = sprintf('%s = ?', $field->link_id_field);
					$result = zModel::select($db, $field->link_table, $filter, null, null, [$this->data->val($field->name)], [PDO::PARAM_STR]);
					if (count($result) > 0) {
						$entity = $result[0];
						$field->link_label = $entity->val($field->link_label_field);
						$field->link_url = sprintf($field->link_template, $entity->val($field->link_id_field));
					} else {
						$field->link_label = null;
						$field->link_url = null;
					}
				} elseif ($field->type == 'multiselect') {
					// prepare select filter
					$field->select_data = zModel::select(
						$db,
						$field->select_table, /* table */
						null, /* where */
						$field->select_label_field, /* orderby */
						null,/* limit */
						null, /* bindings */
						null /* types */
					);
					$selected = zModel::select(
						$db,
						$field->multi_ref_table, /* table */
						sprintf('%s = ?', $field->multi_fk_id_field), /* where */
						null,
						null,
						[$data->ival($field->multi_id_field)], /* bindings */
						[PDO::PARAM_INT] /* types */
					);
					$field->selected_items = zModel::columnAsArray($selected, $field->multi_fk_other_id_field, 'i');
				} elseif ($field->type == 'opening_hours') {
					$field->value = [];
					for ($d = 1; $d <= 7; $d++) {
						$day_name = $this->openinghours_module->getDayName($d);
						$from = $field->prefix . $day_name . '_from';
						$to = $field->prefix . $day_name . '_to';
						$field->value[$from] = $data->val($from);
						$field->value[$to] = $data->val($to);
					}
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
			&& Self::validate_max($value, 99999);
	}

}
