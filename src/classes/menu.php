<?php

/**
* This class simplifies generation of html menus.
*/
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
	
	public function loadItemsFromArray($custom_items) {
		if (isset($custom_items) && count($custom_items) > 0) {
			foreach ($custom_items as $item) {
				if (is_array($item[0])) {
					$submenu = $this->addSubMenu($item[1]);
					$submenu->loadItemsFromArray($item[0]);					
				} else {
					$this->addItem($item[0], $item[1]);
				}
			}
		}
	}
}