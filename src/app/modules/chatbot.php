<?php

/**
* Module that provides chatbot functionality.
*/
class chatbotModule extends zModule {

	public array $depends_on = ['errorlog', 'resources', 'cookies', 'chatgpt'];
	public array $also_install = [];

	public function getPlacement() {
		return $this->getConfigValue('placement', 'bottom');
	}

	public function onBeforeRender() {
		$this->z->core->includeCSS('resources/chat/chat.css', $this->getPlacement());
		$this->z->core->includeJS('resources/chat/chatbot.js', $this->getPlacement());
		$this->z->core->insertJS(
			[
				'z_chatbot' => [
					'url' => $this->getConfigValue('url', $this->z->core->url('json/default/chatbot')),
					'started' => false,
					'messages_delay' => $this->getConfigValue('messages_delay', 1000),
					'auto_start' => $this->getConfigValue('auto_start', false),
					'auto_start_delay' => $this->getConfigValue('auto_start_delay', 0),
					'start_message' => $this->getConfigValue('start_message', ''),
				]
			],
			$this->getPlacement()
		);
		$this->z->core->includePartial('chat', $this->getPlacement());
	}

}
