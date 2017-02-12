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
		$this->available_languages = LanguageModel::all($this->z->core->db);
			
		// select Language
		// TO DO: make this configurable, add selection based on custauth
		
		if (isset($_COOKIE[$this->cookie_name])) {
			$lang_code = $_COOKIE[$this->cookie_name];
		} elseif (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {				
			$lang_code = strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2));
		} elseif (isset($this->config) && isset($this->config['default_language'])) {
			$lang_code = $this->config['default_language'];
		}
		
		if (isset($lang_code)) {
			$this->selected_language = zModel::find($this->available_languages, 'language_code', $lang_code);
		}
		
		if (!isset($this->selected_language)) {
			$this->selected_language = $this->available_languages[0];
		}
		
		$this->loadLanguage($this->config['localization_dir'], $this->selected_language->val('language_code'));
		
		
		// CURRENCY		
		$this->available_currencies = CurrencyModel::all($this->z->core->db);
		$this->selected_currency = zModel::find($this->available_currencies, 'currency_id', $this->selected_language->val('language_default_currency_id'));
	}
	
	function loadLanguage($dir, $lang_code) {		
		$file = $dir . $lang_code . '.php';		
		if (file_exists($file)) {			
			$this->language_data = include $file;
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