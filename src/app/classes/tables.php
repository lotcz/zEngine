<?php

/**
* This class represents a table for tables module.
*/
class zTable {

	public $name;
	public $edit_link;
	public $new_link;
	public $css;
	public $id_field_name;
	public $no_data_message = 'No records were found.';
	public $filter_form = null;

	public $where = null;
	public $bindings = null;
	public $types = null;

	public $orderby = null;
	public $fields = [];
	public $data = [];

	function __construct($name = 'table or view', $css = '') {
		$this->name = $name;
		$this->css = $css;
	}

	public function addField($field) {
		$this->fields[$field['name']] = (object)$field;
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

	public function addLink($url, $title) {
		$this->links[] = ['url' => $url, 'title' => $title];
	}

}
