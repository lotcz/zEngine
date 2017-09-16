<?php
	$this->requireModule('mysql');
	$this->requireModule('i18n');
	require_once __DIR__ . '/../models/translation.m.php';
	$db = $this->z->core->db;
	$languages = LanguageModel::all($db);

	zSqlQuery::executeSQL($db, 'delete from translations');

	foreach ($languages as $language) {
		$language_data = $this->z->i18n->loadLanguageData($language->val('language_code'));
		foreach ($language_data as $name => $translation) {
			$t = new TranslationModel($db);
			$t->load($language->ival('language_id'), $name);
			$t->data['translation_name'] = $name;
			$t->data['translation_translation'] = $translation;
			$t->data['translation_language_id'] = $language->ival('language_id');
			$t->save();
		}
	}

	echo 'Translations imported.';
