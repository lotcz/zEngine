<?php	
	require_once __DIR__ . '/../../../models/language.m.php';
	$this->renderAdminForm(
		'language',
		'LanguageModel',
		[		
			[
				'name' => 'language_name',
				'label' => 'Name',
				'type' => 'text',
				'validations' => [
					['type' => 'length', 'param' => 1],
					['type' => 'maxlen', 'param' => 100]
				]
			],
			[
				'name' => 'language_code',
				'label' => 'Code',
				'type' => 'text',
				'validations' => [
					['type' => 'length', 'param' => 1],
					['type' => 'maxlen', 'param' => 10]
				]
			],
			[
				'name' => 'language_decimal_separator',
				'label' => 'Decimal Separator',
				'type' => 'text',
				'validations' => [
					['type' => 'length', 'param' => 1],
					['type' => 'maxlen', 'param' => 10]
				]
			],
			[
				'name' => 'language_thousands_separator',
				'label' => 'Thousand Separator',
				'type' => 'text',
				'validations' => [
					['type' => 'length', 'param' => 1],
					['type' => 'maxlen', 'param' => 10]
				]
			],
			[
				'name' => 'language_default_currency_id',
				'label' => 'Default Currency',
				'type' => 'select',
				'select_table' => 'currencies',
				'select_id_field' => 'currency_id',
				'select_label_field' => 'currency_name'
			]
		]
	);