<?php

class zMessage {
	
	public $type = 'info';	
	public $text = '';
	
	function __construct($text, $type = 'info') {		
		$this->type = $type;
		$this->text = $text;
	}
	
	public function render() {
		$class = $this->type;
		$prefix = '';		
		
		return sprintf('<div class="alert alert-%s">%s %s</div>', $class, $prefix, $this->text);
	}

}