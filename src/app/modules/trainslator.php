<?php

/**
* Module that automatically translates text using chatGPT API
*/
class trainslatorModule extends zModule {

	public $depends_on = ['i18n', 'chatgpt'];

	public $also_install = [];

	private function getLanguageName(?string $lang) {
		return $lang ?? $this->z->i18n->selected_language->val('language_name');
	}

	public function translateText(string $text, ?string $language = null) {
		return $this->z->chatgpt->ask(
			[
				"Translate to {$this->getLanguageName($language)}",
				$text
			],
			"You are helpful automatic translator that accurately translate texts to different languages."
		);
	}

	public function translateHTML(string $html, ?string $language = null) {
		return $this->z->chatgpt->ask(
			[
				"Translate to {$this->getLanguageName($language)}",
				$html
			],
			"You are helpful automatic translator that accurately translate web content to different languages. You always keep HTML tags structure in place and keep all attribute values so translated content can be safely displayed on web."
		);
	}

}
