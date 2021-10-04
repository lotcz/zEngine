<?php

	/*
		MODE

		fit (default value) - image will preserve aspect and always be the same or smaller than given size 
		crop - image will be cropped to be exactly the given size
		scale - scale to new format without preserving aspect
	*/

	return [
		// available formats for image resizing
		'formats' => [
			'mini' => ['width' => 75, 'height' => 50 ],
			'thumb' => ['width' => 160, 'height' => 140 ],
			'view' => ['width' => 320, 'height' => 200 ]
		],

		// absolute path to disk where all images are stored, include trailing slash
		'images_disk_path' => 'C:\\images\\path\\',

		// base url for images src, no trailing slash
		'images_url' => 'http://images.url',

		'no_image' => 'no-image.jpg',

		'image_not_found' => 'image-not-found.jpg',
	];
