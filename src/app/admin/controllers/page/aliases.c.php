<?php

	$this->z->admin->checkAnyRole();

	$this->setPageTitle('Aliases');
	$this->renderAdminTable(
		'alias',
		[
			[
				'name' => 'alias_url',
				'label' => 'URL'
			],
			[
				'name' => 'alias_path',
				'label' => 'Path'
			],
		],
		null,
		['alias_url', 'alias_path'],
		'alias_url',
		['alias_url', 'alias_path']
	);
