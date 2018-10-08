<?php

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
		]
	);
