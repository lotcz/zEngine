<?php

class zTable {
	
	public $name;
	public $edit_link;	
	public $new_link;	
	public $css;
	public $id_field;
	
	public $show_search = false;
	
	public $bindings = null;
	public $types = null;
	public $where = null;
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
		$this->search = isset($_GET['s']) ? $_GET['s'] : '';
		
		// add filtering logic here
	
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
