<?php

	$this->requireModule('gallery');

	$this->renderAdminForm(
		'GalleryModel',
		[
			[
				'name' => 'gallery_name',
				'label' => 'Name',
				'type' => 'text'
			],
			[
				'name' => 'gallery_id',
				'label' => 'Gallery',
				'type' => 'gallery'
			]
		],
	);
