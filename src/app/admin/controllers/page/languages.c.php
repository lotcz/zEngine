<?php
	
	$this->setPageTitle('Languages');
	$this->renderAdminTable(
		'languages', 		
		'language',
		[		
			[
				'name' => 'language_name',
				'label' => 'Name'		
			],
			[
				'name' => 'language_code',
				'label' => 'Code'			
			]
		]
	);