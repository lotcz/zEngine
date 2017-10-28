<?php

	$this->setPageTitle('Static pages');
	$this->renderAdminTable(
		'viewStaticPages',
		'static_page',
		[
			[
				'name' => 'static_page_title',
				'label' => 'Title'
			],
			[
				'name' => 'alias_url',
				'label' => 'Alias'
			],
		]
	);
