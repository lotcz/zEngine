<?php

	$this->setPageTitle('Translations');
	$this->renderAdminTable(
		'viewTranslations',
		'translation',
		[
			[
				'name' => 'translation_name',
				'label' => 'Name'
			],
			[
				'name' => 'translation_translation',
				'label' => 'Translation'
			],
			[
				'name' => 'language_name',
				'label' => 'Language'
			]
		]
	);
