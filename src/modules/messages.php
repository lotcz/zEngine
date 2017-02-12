<?php

class Message {
	
	public $type = 'info';	
	public $text = '';
	
	function __construct($text, $type = 'info') {		
		$this->type = $type;
		$this->text = $text;
	}
	
	public function render() {
		$class = $this->type;
		$prefix = '';
		
		switch ($this->type) {
			case 'error':
				$class = 'danger';
				$prefix = '<strong>' . t('Error') . ':</strong>';
				break;
			case 'db-error':
				$class = 'danger';
				$prefix = '<strong>DB Error:</strong> ';
				break;
		}
		
		return sprintf('<div class="alert alert-%s">%s %s</div>', $class, $prefix, $this->text);
	}

}

class Messages {

	public $messages = [];
	
	public function add($text, $type = 'info') {
		$this->messages[] = new Message($text, $type);
	}
	
	public function clear() {
		$this->messages = [];
	}
	
	public function error($text) {
		$this->add($text, 'error');
	}
	
	public function dbErr($model, $operation, $sql, $message) {
		$this->add(sprintf('\'%s\' in model %s during operation \'%s\'. <br/>SQL: %s', $message, $model, $operation, $sql), 'db-error');
	}
	
	public function render() {
		if (count($this->messages) > 0) {
			?>
				<div class="spaced well messages">
					<?php
						foreach ($this->messages as $m) {				
							echo $m->render();
						}
					?>
				</div>
			<?php
		}
	}

}