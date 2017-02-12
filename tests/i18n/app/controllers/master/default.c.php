<?php

	$this->data['language_name'] = $this->z->i18n->selected_language->val('language_name');
	$this->data['languages'] = $this->z->i18n->available_languages;
	$this->data['currencies'] = $this->z->i18n->available_currencies;