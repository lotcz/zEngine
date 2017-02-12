<?php

class globalsModule extends zModule {
	
	private $db = null;
	public $values = [];
	
	function onEnabled() {		
		$this->db = $this->z->db->connection;
		$stmt = SqlQuery::select($this->db, 'site_globals');
		$result = $stmt->get_result();		
		while ($row = $result->fetch_assoc()) {			
			$this->values[$row['site_global_name']] = $row['site_global_value'];
		}
		$stmt->close();
	}
	
	function getForm($action) {
		$form = new Form('site_globals', $action);
	
		foreach ($this->values as $key => $value) {
			$form->addField([
				'name' => $key,
				'label' => $key,
				'type' => 'text',
				'value' => $value
			]);
		}
		
		return $form;
	}
	
	function val($key, $def = null) {
		if (!isset($this->values[$key])) {
			return $this->values[$key];
		} else {
			return $def;
		}
	}
	
	function processForm($form) {
		$values = $form->processInput($_POST);
		
		foreach ($values as $key => $value) {
			SqlQuery::update($this->db, 'site_globals', ['site_global_value' => $value], 'site_global_name = ?', [$key]);
			$this[$key] = $value;
		}
		
	}
	
}