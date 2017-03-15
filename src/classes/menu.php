<?php

class zMenu {
		
	public $href = '';
	public $label = '';
	public $type = 'item';
	public $items = [];
	public $right_items = [];
	
	public function __construct($href = null, $label = '', $type = 'item') {
		$this->href = $href;
		$this->label = $label;
		$this->type = $type;
	}
	
	public function addItem($href, $label) {
		$this->items[] = new zMenu($href, $label);
	}
	
	public function prependItem($href, $label) {
		array_unshift($this->items, new zMenu($href, $label));
	}
	
	public function addSeparator() {
		$this->items[] = new zMenu(null, null, 'separator');
	}
	
	public function addHeader($label) {
		$this->items[] = new zMenu(null, $label, 'header');
	}
	
	public function addSubMenu($label) {
		$submenu = new zMenu(null, $label, 'submenu');
		$this->items[] = $submenu;
		return $submenu;
	}	
		
	public function addRightItem($href, $label) {
		$this->right_items[] = new zMenu($href, $label);
	}
			
}