<?php

/**
* This class simplifies generation of paged html tables.
*/
class zTable {

	public $name;
	public $edit_link;
	public $new_link;
	public $css;
	public $id_field;

	public $filter_form = null;

	public $where = null;
	public $bindings = null;
	public $types = null;

	public $orderby = null;
	public $fields = [];
	public $data = [];

	public $page_size = 20;
	public $max_pages_links = 20;

	function __construct($name = 'table or view', $id_field = '', $edit_link = '', $new_link = '', $css = '') {
		$this->name = $name;
		$this->id_field = $id_field;
		$this->edit_link = $edit_link;
		$this->new_link = $new_link;
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

	public function prepare($db) {
		$this->paging = zPaging::getFromUrl();
		$this->paging->limit = $this->page_size;
		zPaging::$max_pages_links = $this->max_pages_links;

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

		$this->data = zModel::select(
			$db,
			$this->name,
			$this->where,
			$this->bindings,
			$this->types,
			$this->paging,
			$this->orderby
		);

	}

}

class zAdminTable extends zTable {

	public $links = [];

	function __construct($view_name = 'table or view', $entity_name = 'entity') {
		parent::__construct(
			$view_name,
			$entity_name . '_id',
			sprintf('admin/default/default/%s/edit/', $entity_name) . '%d',
			sprintf('admin/default/default/%s', $entity_name),
			'table-striped table-hover'
		);
	}

	public function addLink($url, $title) {
		$this->links[] = ['url'=>$url,'title'=>$title];
	}
}
