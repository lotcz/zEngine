<?php

	$this->z->admin->checkAnyRole();

	$shorten = function ($str) {
		return z::shorten(z::stripHtmlTags($str, ''));
	};

	$this->setPageTitle('AI Cache');
	$this->renderAdminTable(
		'trainslator_cache',
		[
			[
				'name' => 'trainslator_cache_id',
				'label' => 'ID'
			],
			[
				'name' => 'trainslator_cache_key_hash',
				'label' => 'Hash'
			],
			[
				'name' => 'trainslator_cache_key',
				'label' => 'Key',
				'type' => 'custom',
				'custom_function' => $shorten
			],
			[
				'name' => 'trainslator_cache_value',
				'label' => 'Value',
				'type' => 'custom',
				'custom_function' => $shorten
			],
			[
				'name' => 'language_name',
				'label' => 'Language'
			],
		],
		'view_trainslator_cache',
		['trainslator_cache_id', 'trainslator_cache_key_hash', 'trainslator_cache_key', 'trainslator_cache_value', 'language_name'],
		'trainslator_cache_id desc',
		['trainslator_cache_key', 'trainslator_cache_value']
	);
