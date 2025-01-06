<?php

	$this->requireModule('chatbot');

	$json = z::getPostJson();
	$message = $json['message'];
	$json = [];
	$json['response'] = $this->z->chatgpt->ask($message);

	$this->setData('json', $json);
