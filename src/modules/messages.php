<?php

require_once __DIR__ . '/../classes/message.php';

class messagesModule extends zModule{

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
