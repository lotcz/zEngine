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

	function __construct($entity_name = 'table or view', $view_name = null, $css = '') {
		$this->entity_name = $entity_name;		
		if (!isset($view_name)) {
			$view_name = $entity_name;
		}
		$this->view_name = $view_name;
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

	public function prepare($db, $default_paging = null) {
		$this->paging = zPaging::getFromUrl($default_paging);

		// filtering
		if (isset($this->filter_form) && z::isPost()) {
			$filter_values = $this->filter_form->processed_input;
			$where = [];
			$this->bindings = [];
			$this->types = '';
			foreach ($this->filter_form->fields as $field) {
				if ($field->type == 'text') {
					foreach ($field->filter_fields as $filter_field) {
						$field->value = $filter_values[$field->name];
						if (strlen($filter_values[$field->name]) > 0) {
							$where[] = sprintf('%s like ?', $filter_field);
							$this->bindings[] = '%' . $filter_values[$field->name] . '%';
							$this->types .= 's';
						}
					}
				}
			}
			if (count($where)) {
				$this->where = implode($where, ' or ');
			} else {
				$this->where = null;
				$this->bindings = null;
				$this->types = null;
			}
		}

		$this->paging->total_records = $db->getRecordCount(
			$this->name,
			$this->where,
			$this->bindings,
			$this->types
		);

		$this->data = zModel::select(
			$db,
			$this->name,
			$this->where,
			$this->paging->getOrderBy(),
			$this->paging->getLimit(),
			$this->bindings,
			$this->types
		);

	public function prepare($db) {
		$this->paging = zPaging::getFromUrl();

		// filtering
		if (isset($this->filter_form) && z::isPost()) {
			$filter_values = $this->filter_form->processed_input;
			$where = [];
			$this->bindings = [];
			$this->types = '';
			foreach ($this->filter_form->fields as $field) {
				if ($field->type == 'text') {
					foreach ($field->filter_fields as $filter_field) {
						$field->value = $filter_values[$field->name];
						if (strlen($filter_values[$field->name]) > 0) {
							$where[] = sprintf('%s like ?', $filter_field);
							$this->bindings[] = '%' . $filter_values[$field->name] . '%';
							$this->types .= 's';
						}
					}
				}
			}
			if (count($where)) {
				$this->where = implode($where, ' or ');
			} else {
				$this->where = null;
				$this->bindings = null;
				$this->types = null;
			}
		}

		$this->paging->total_records = $db->getRecordCount(
			$this->name,
			$this->where,
			$this->bindings,
			$this->types
		);
		
		$this->data = zModel::select(
			$db,
			$this->name,
			$this->where,
			$this->paging->getOrderBy(),
			$this->paging->getLimit(),
			$this->bindings,
			$this->types
		);

	public function addLink($url, $title) {
		$this->links[] = ['url' => $url, 'title' => $title];
	}

}
