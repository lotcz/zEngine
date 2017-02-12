<?php

	$new_currency_id = get('currency_id');
	if (isset($new_currency_id)) {
		$this->z->i18n->selectCurrency($new_currency_id);
	}
	
	$new_language_id = get('language_id');
	if (isset($new_language_id)) {
		$this->z->i18n->selectLanguage($new_language_id);
	}