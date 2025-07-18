<?php

	return [

		// TinyMCE configuration, will be serialized to JSON.
		'tinymce_conf' => [
			'selector' => 'textarea.wysiwyg',
			'language' => 'cs',
			'autoresize' => false,
			'resize' => true,
			'height' => 500,
			'branding' => false,
			'menubar' => false,
			'plugins' => "wordcount link lists image code",
			'toolbar' => "undo redo | blocks | bold italic underline strikethrough | alignleft aligncenter alignright | bullist numlist | image | link unlink | pastetext code | wordcount",
			'paste_as_text' => true,
			'image_caption' => true,
			'image_advtab' => true,
			'image_dimensions' => false,
			'relative_urls' => false,
			'typeahead_urls' => false,
			'promotion' => false,
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
			['https://zavadil.eu/tinymce6/tinymce.min.js', 'link_js', 'admin.bottom']
		],

		// This will override placement defined for includes above.
		// Change this to 'bottom' if you need to use TinyMCE on public part of the website.
		'default_placement' => 'admin.bottom',

		// function to be run on pasted raw text
		// should be in form (editor, args) => {}
		'tinymce_paste_preprocess' => '(editor, args) => {
			console.log(args.content);
			//args.content = args.content.replace(/ style="[^"]*"/gi, \'\');
		}',

		// function to be run on pasted DOM object (after paste_preprocess)
		// should be in form (editor, args) => {}
		// this removes all 'style' and 'face' attributes
		'tinymce_paste_postprocess' => '(editor, args) => {
			const removeStyles = (node) => {
			  if (node.nodeType === 1) {
				node.removeAttribute(\'style\');
				node.removeAttribute(\'face\');
				for (let i = 0; i < node.childNodes.length; i++) {
				  removeStyles(node.childNodes[i]);
				}
			  }
			};		
    		removeStyles(args.node);
		}'
	];
