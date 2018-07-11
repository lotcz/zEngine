<?php

/**
* Class that represents a single message in message queue.
*/
class zMessage {
	
	public $type = 'info';	
	public $text = '';
	
	function __construct($text, $type = 'info') {		
		$this->type = $type;
		$this->text = $text;
	}
	
}

/**
* Module that handles queuing and displaying of messages.
*/
class messagesModule extends zModule{

	public $messages = [];
	
	public function add($text, $type = 'info') {
		$this->messages[] = new zMessage($text, $type);
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
				<div class="spaced messages">
					<?php
						foreach ($this->messages as $m) {
							?>
								<div class="alert alert-<?=($m->type == 'error') ? 'danger' : $m->type ?>">
									<?=$m->text ?>
								</div>
							<?php
						}
					?>
				</div>
			<?php
		}
	}

}
