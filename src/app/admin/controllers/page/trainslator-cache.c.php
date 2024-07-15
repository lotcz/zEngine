<?php

$this->requireModule('trainslator');

$this->renderAdminForm(
	'TrainslatorCacheModel',
	[
		[
			'name' => 'trainslator_cache_key_hash',
			'label' => 'Hash',
			'type' => 'static'
		],
		[
			'name' => 'trainslator_cache_language_id',
			'label' => 'Language',
			'type' => 'select',
			'select_data' => LanguageModel::all($this->z->db),
			'select_id_field' => 'language_id',
			'select_label_field' => 'language_name'
		],
		[
			'name' => 'trainslator_cache_key',
			'label' => 'Key',
			'type' => 'textarea'
		],
		[
			'name' => 'trainslator_cache_value',
			'label' => 'Value',
			'type' => 'textarea'
		]
	],
);
