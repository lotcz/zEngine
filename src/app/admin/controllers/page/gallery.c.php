<?php

	require_once __DIR__ . '/../../../models/gallery.m.php';

	$this->requireModule('gallery');

	$this->renderAdminForm(
		'GalleryModel',
		[
			[
				'name' => 'gallery_name',
				'label' => 'Name',
				'type' => 'text'
			]
		]
	);
