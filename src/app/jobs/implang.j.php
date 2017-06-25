<?php
	global $home_dir, $language_data;
	require_once $home_dir . 'classes/localization.php';
	require_once $home_dir . 'models/translation.m.php';
	require_once $home_dir . 'models/language.m.php';
		
	$localization = new Localization($home_dir . 'lang/');
	
	$language = new Language($db);
	$language->loadByCode($language_data['language_code']);
	
	foreach ($language_data as $name => $translation) {
		$t = new Translation($db);
		$t->load($language->ival('language_id'), $name);		
		$t->data['translation_name'] = $name;	
		$t->data['translation_translation'] = $translation;		
		$t->data['translation_language_id'] = $language->ival('language_id');
		$t->save();
	}
	
	echo 'Language imported.';