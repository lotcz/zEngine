<?php

/**
* Module that provides chatbot functionality.
*/
class chatbotModule extends zModule {

	public $depends_on = ['resources', 'cookies'];
	public $also_install = [];

	public function onBeforeRender() {
		$this->z->core->includeCSS('resources/chat.css');
		$this->z->core->includeJS('resources/chatbot.js');
		$this->z->core->insertJS(
			[
				'z_chatbot' => [
					'url' => $this->getConfigValue('url', $this->z->core->url('chatbot')),
					'started' => false,
					'messages_delay' => $this->getConfigValue('messages_delay', 1000),
					'auto_start' => $this->getConfigValue('auto_start', false),
					'auto_start_delay' => $this->getConfigValue('auto_start_delay', 0),
					'start_message' => $this->getConfigValue('start_message', ''),
				]
			]
		);
		$this->z->core->includePartial('chat', 'bottom');
	}

}
