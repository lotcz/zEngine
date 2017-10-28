<?php
	$this->requireModule('mysql');
	$this->requireModule('i18n');
	require_once __DIR__ . '/../models/translation.m.php';
	$db = $this->z->core->db;
	$languages = LanguageModel::all($db);

	foreach ($languages as $language) {
		$file_path = $this->z->i18n->getLanguageFilePath($language->val('language_code'));
		$file = fopen($file_path, 'w');
		fwrite($file, '<?php' . PHP_EOL);
		fwrite($file, '$language_data = [];' . PHP_EOL);
		$translations = TranslationModel::select(
			$db,
			'translations',
			'translation_language_id = ?',
			[$language->ival('language_id')],
			'i'
		);

		foreach ($translations as $translation) {
			fwrite($file, sprintf('$language_data[\'%s\'] = \'%s\';', z::escapeSingleQuotes($translation->val('translation_name')), z::escapeSingleQuotes($translation->val('translation_translation'))) . PHP_EOL);
		}

		fwrite($file, 'return $language_data;' . PHP_EOL);
		fclose($file);
	}

	echo 'Translations exported.';
