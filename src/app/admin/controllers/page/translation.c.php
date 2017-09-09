<?php
	require_once __DIR__ . '/../../../models/translation.m.php';
	$this->renderAdminForm(
		'translation',
		'TranslationModel',
		[
			[
				'name' => 'translation_language_id',
				'label' => 'Language',
				'type' => 'select',
				'select_table' => 'languages',
				'select_data' => LanguageModel::all($this->db),
				'select_id_field' => 'language_id',
				'select_label_field' => 'language_name'
			],
			[
				'name' => 'translation_name',
				'label' => 'Name',
				'type' => 'text',
				'validations' => [
					['type' => 'length', 'param' => 1],
					['type' => 'maxlen', 'param' => 255]
				]
			],
			[
				'name' => 'translation_translation',
				'label' => 'Translation',
				'type' => 'text',
				'validations' => [
					['type' => 'length', 'param' => 1]
				]
			]
		]
	);
