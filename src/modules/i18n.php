<?php

require_once __DIR__ . '/../models/language.m.php';
require_once __DIR__ . '/../models/currency.m.php';

class i18nModule extends zModule {
	
	public $cookie_name = 'language';
	public $language_data = null;
	public $available_languages = null;
	public $selected_language = null;
	public $available_currencies = null;
	public $selected_currency = null;
	
	public function onEnabled() {
		
		// LANGUAGE
		
		$this->available_languages = LanguageModel::all($this->z->core->db);
		
		// TO DO: if custauth Module is enabled then use customers default language
		//
		
		// try to use browser's language if available
		if (!isset($this->selected_language)) {
			if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
				$this->selected_language = zModel::find($this->available_languages, 'language_code', strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2)));
			}
		}
		
		// fallback to default language
		if (!isset($this->selected_language)) {
			$this->selected_language = zModel::find($this->available_languages, 'language_code', $this->config['default_language']);
		}
		
		$this->loadLanguage($this->selected_language->val('language_code'));
		
		// CURRENCY
		
		$this->available_currencies = CurrencyModel::all($this->z->core->db);
		$this->selectCurrency($this->selected_language->ival('language_default_currency_id'));
	}
	
	public function selectLanguage($language_id) {
		$this->selected_language = zModel::find($this->available_languages, 'language_id', $language_id);
		$this->loadLanguage($this->selected_language->val('language_code'));		
		$this->selectCurrency($this->selected_language->ival('language_default_currency_id'));
	}
	
	public function selectCurrency($currency_id) {
		$this->selected_currency = zModel::find($this->available_currencies, 'currency_id', $currency_id);
	}
	
	function loadLanguage($lang_code) {		
		$file = $this->config['localization_dir'] . $lang_code . '.php';		
		if (file_exists($file)) {			
			$this->language_data = include $file;
		} else {
			$this->language_data = null;
		}
	}
		
	public function translate($s) {		
		if (isset($this->language_data) and isset($this->language_data[$s])) {
			$t = $this->language_data[$s];		
		} else {
			$t = $s;
		}
		return $t;		
	}
	
	public function formatMoney($price) {
		return sprintf($this->selected_currency->val('currency_format'), number_format($price, $this->selected_currency->ival('currency_decimals'), $this->selected_language->val('language_decimal_separator'), $this->selected_language->val('langauge_thousands_separator')));
	}
	
	public function convertMoney($price) {
		return $this->selected_currency->convert($price);
	}
	
	// return javascript equivalent of formatMoney and convertMoney
	public function jsFormatPrice($db, $selected_currency = null) {
		if (!isset($selected_currency)) {			
			$selected_currency = Currency::getSelectedCurrency($db);
		}
		$s = sprintf('function convertPrice(price) { return price / %d; }', $selected_currency->fval('currency_value'));
		$s.= sprintf('function formatPrice(price) { return (\'%s\').replace(\'%s\', price.formatMoney(%d, \'%s\', \'%s\')); }', $selected_currency->val('currency_format'), '%s', $selected_currency->ival('currency_decimals'), t('decimal_separator'), t('thousands_separator') );
		return $s;
	}
	
	// experimental, not used
	public function saveUntranslated() {
		require_once $home_dir . 'models/translation.m.php';
		$language = new Language($db);
		$language->loadByCode($language_data['language_code']);
		$t = new Translation($db);
		$t->load($language->ival('language_id'), $s);
		if (!$t->is_loaded) {
			$t->data['translation_name'] = $s;
			$t->data['translation_language_id'] = $language->ival('language_id');
			$t->save();
		}
	}
	
}