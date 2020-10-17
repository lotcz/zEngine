<?php

	return [
		// available formats for image resizing
		'formats' => [
			'mini-thumb' => ['width' => 75, 'height' => 50 ],
			'thumb' => ['width' => 160, 'height' => 140 ],
			'view' => ['width' => 320, 'height' => 200 ]
		],

		// absolute path to disk where all images are stored, include trailing slash
		'images_disk_path' => 'C:\\images\\path\\',

		// base url for images src, no trailing slash
		'images_url' => 'http://images.url'
	];
