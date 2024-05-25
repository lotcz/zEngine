<?php

	return [

		'api_key' => '',

		'model' => 'gpt-3.5-turbo',

		'max_tokens' => 2000,

		/*
		 * What sampling temperature to use, between 0 and 2.
		 * Higher values like 0.8 will make the output more random, while lower values like 0.2 will make it more focused and deterministic.
		 */
		'temperature' => 0.4,

		'api_url' => 'https://api.openai.com/v1/chat/completions',

		'system_prompt' => 'You are a helpful assistant.',

	];
