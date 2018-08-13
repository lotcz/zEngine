<?php

	$this->setPageTitle('Aliases');
	$this->renderAdminTable(
		'alias', 		
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
