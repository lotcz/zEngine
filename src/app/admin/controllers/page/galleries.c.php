<?php

	$this->z->admin->checkAnyRole();

	$this->setPageTitle('Galleries');
	$this->renderAdminTable(
		'gallery',
		[
			[
				'name' => 'gallery_id',
				'label' => 'ID'
			],
			[
				'name' => 'gallery_name',
				'label' => 'Name'
			]
		],
		null,
		['gallery_id', 'gallery_name'],
		'gallery_id'
	);
