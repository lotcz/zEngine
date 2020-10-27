<?php

	return [

		// TinyMCE configuration, will be serialized to JSON.
		'tinymce_conf' => [
			'autoresize' => false,
			'resize' => true,
			'height' => 500,
			'branding' => false,
			'menubar' => false,
			'plugins' => "wordcount link lists paste image code",
			'toolbar' => "undo redo | styleselect | bold italic underline strikethrough | alignleft | bullist numlist | image | link unlink | pastetext code | wordcount",
			'paste_as_text' => true,
			'image_caption' => true,
			'image_advtab' => true,
			'image_dimensions' => false,
			'typeahead_urls' => false,
			'style_formats' => [
				[ 'title' => 'Odstavec', 'format' => 'p' ],
				[ 'title' => 'Nadpis', 'format' => 'h2' ],
				[ 'title' => 'Nadpis 2', 'format' => 'h3' ],
				[ 'title' => 'Nadpis 3', 'format' => 'h4' ],
			]
		],

		// additional js, css etc.
		// EXAMPLE: [file_path, is_absolute, type, placement]
		// file_path - path to file
		// is_absolute - true/false
		// type - link_css/print_css/link_less/link_js/inline_js/favicon
		// placement - head/default/bottom/
		'includes' => [
			['https://zavadil.eu/tinymce/tinymce.min.js', true, 'link_js', 'admin.bottom'],
			['https://zavadil.eu/tinymce/jquery.tinymce.min.js', true, 'link_js', 'admin.bottom'],
		],

		// This will override placement defined for includes above.
		// Change this to 'bottom' if you need to use TinyMCE on public part of the website.
		'default_placement' => 'admin.bottom'
	];
