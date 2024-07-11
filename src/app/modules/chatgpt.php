<?php

/**
* Module that wraps use of chatGPT API
*/
class chatGPTModule extends zModule {

	public $depends_on = [];
	public $also_install = [];

	public function ask(
		$userPrompt,
		?string $systemPrompt = null,
		?float $temperature = null,
		?string $model = null,
		?int $maxTokens = null
	): ?string {
		if (empty($userPrompt)) return 'no input provided';
		$url = $this->getConfigValue('api_url','https://api.openai.com/v1/chat/completions');

		$messages = [
			[
				'role' => 'system',
				'content' => $systemPrompt ?? $this->getConfigValue('system_prompt', 'You are a helpful assistant.')
			],
		];

		$user_messages = (is_array($userPrompt)) ? $userPrompt : [$userPrompt];

		foreach ($user_messages as $user_message) {
			$messages[] = [
				'role' => 'user',
				'content' => $user_message
			];
		}

		$data = [
			'model' => $model ?? $this->getConfigValue('model', 'gpt-3.5-turbo'),
			'messages' => $messages,
			'max_tokens' => $maxTokens ?? $this->getConfigValue('max_tokens', 2000),
			'temperature' => $temperature ?? $this->getConfigValue('temperature',0.3)
		];

		$headers = [
			'Content-Type: application/json',
			'Authorization: Bearer ' . $this->getConfigValue('api_key'),
		];

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$response = curl_exec($ch);
		curl_close($ch);

		$responseData = json_decode($response, true);
		//$this->z->errorlog->write(print_r($responseData, true));
		return $responseData['choices'][0]['message']['content'] ?? null;
	}

}
