<?php

	return [

		/* show standard admin menu */
		'show_default_menu' => true,

		/* custom app admin menu */
		'custom_menu' => [
		],

		// additional js, css etc.
		// EXAMPLE: [file_path, is_absolute, type, placement]
		// file_path - path to file
		// is_absolute - true/false
		// type - link_css/print_css/link_less/link_js/inline_js/favicon
		// placement - head/default/bottom
		'includes' => [
			['favicon.ico', false, 'favicon', 'admin.head']
		]
	];
