<?php

	require_once __DIR__ . '/../../../models/alias.m.php';

	$this->z->admin->checkAnyRole();

	$this->renderAdminForm(
		'AliasModel',
		[
			[
				'name' => 'alias_url',
				'label' => 'URL',
				'type' => 'text',
				'validations' => [
					['type' => 'length', 'param' => 1],
					['type' => 'maxlen', 'param' => 191]]
			],
			[
				'name' => 'alias_path',
				'label' => 'Path',
				'type' => 'text',
				'validations' => [
					['type' => 'length', 'param' => 1],
					['type' => 'maxlen', 'param' => 255]
				]
			]

		]
	);
