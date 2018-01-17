<?php

require_once __DIR__ . '/../app/models/language.m.php';
require_once __DIR__ . '/../app/models/currency.m.php';

/**
* Module that handles application internationalization
* Things like string translation and number and date formatting are handled by this module.
*/
class i18nModule extends zModule {

	private $db = null;
	public $language_cookie_name = 'language';
	public $currency_cookie_name = 'currency';
	public $language_data = null;
	public $available_languages = null;
	public $selected_language = null;
	public $available_currencies = null;
	public $selected_currency = null;

	public function onEnabled() {
		$this->requireModule('mysql');
		$this->db = $this->z->core->db;

		$this->language_cookie_name = $this->getConfigValue('language_cookie_name', $this->language_cookie_name);
		$this->currency_cookie_name = $this->getConfigValue('currency_cookie_name', $this->currency_cookie_name);

	}

	public function onInit() {

		$this->available_currencies = CurrencyModel::all($this->db);
		$this->available_languages = LanguageModel::all($this->db);

		if (!$this->getConfigValue('force_default_language')) {

			// first, use currency and language from cookies, if available
			if (isset($_COOKIE[$this->currency_cookie_name])) {
				$this->selectCurrencyByID($_COOKIE[$this->currency_cookie_name]);
			}
			if (isset($_COOKIE[$this->language_cookie_name])) {
				$this->selectLanguageByID($_COOKIE[$this->language_cookie_name]);
			}

			if ($this->z->moduleEnabled('custauth') && $this->z->custauth->isAuth()) {
				// update customer default currency if different from cookie values
				if (isset($this->selected_currency)) {
					if ($this->z->custauth->customer->ival('customer_currency_id') != $this->selected_currency->ival('currency_id')) {
						$this->z->custauth->customer->set('customer_currency_id', $this->selected_currency->ival('currency_id'));
						$this->z->custauth->customer->save($this->db);
					}
				} else {
					// use saved customer defaults otherwise
					$this->selectCurrencyByID($this->z->custauth->ival('customer_currency_id'));
				}

				// update customer default language if different from cookie values
				if (isset($this->selected_language)) {
					if ($this->z->custauth->customer->ival('customer_language_id') != $this->selected_language->ival('language_id')) {
						$this->z->custauth->customer->set('customer_language_id', $this->selected_language->ival('language_id'));
						$this->z->custauth->customer->save($this->db);
					}
				} else {
					// use saved customer defaults otherwise
					$this->selectLanguageByID($this->z->custauth->ival('customer_language_id'));
				}
			}

			// try to use browser's language if available
			if (!isset($this->selected_language)) {
				if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
					$this->selectLanguageByCode(strtolower(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'],0,2)));
				}
			}

		}

		// fallback to default language
		if (!isset($this->selected_language)) {
			$this->selectLanguageByCode($this->getConfigValue('default_language'));
		}

	}

	public function selectLanguage($language) {
		$this->selected_language = $language;
		if (isset($this->selected_language)) {
			$this->loadLanguage($this->selected_language->val('language_code'));
			if (!isset($this->selected_currency)) {
				$this->selectCurrencyByID($this->selected_language->ival('language_default_currency_id'));
			}
		}
	}

	public function selectLanguageByID($language_id) {
		$this->selectLanguage(zModel::find($this->available_languages, 'language_id', $language_id));
	}

	public function selectLanguageByCode($language_code) {
		$this->selectLanguage(zModel::find($this->available_languages, 'language_code', $language_code));
	}

	public function selectCurrency($currency) {
		$this->selected_currency = $currency;
	}

	public function selectCurrencyByID($currency_id) {
		$this->selectCurrency(zModel::find($this->available_currencies, 'currency_id', $currency_id));
	}

	public function getLanguageFilePath($lang_code) {
		return $this->getConfigValue('localization_dir') . $lang_code . '.php';
	}

	public function loadLanguageData($lang_code) {
		$file_path = $this->getLanguageFilePath($lang_code);
		if (file_exists($file_path)) {
			return include $file_path;
		}
	}

	public function loadLanguage($lang_code) {
		$this->language_data = $this->loadLanguageData($lang_code);
	}

	// directly translates string if translation exists
	// if you want to process tokens inside, use t function of core module instead
	public function translate($s) {
		if (isset($this->language_data) and isset($this->language_data[$s])) {
			$t = $this->language_data[$s];
		} else {
			$t = $s;
		}
		return $t;
	}

	public function formatMoney($price) {
		return $this->selected_currency->format(number_format($price, $this->selected_currency->ival('currency_decimals'), $this->selected_language->val('language_decimal_separator'), $this->selected_language->val('language_thousands_separator')));
	}

	public function convertMoney($price) {
		return $this->selected_currency->convert($price);
	}

	public function formatDate($date) {
		return $this->selected_language->formatDate($date);
	}

	public function formatDatetime($date) {
		return $this->selected_language->formatDatetime($date);
	}

	// return javascript equivalent of formatMoney and convertMoney
	public function jsFormatPrice($selected_currency = null) {
		if (!isset($selected_currency)) {
			$selected_currency = $this->selected_currency;
		}
		$s = sprintf('function convertPrice(price) { return price / %d; }', $selected_currency->fval('currency_value'));
		$s.= sprintf('function formatPrice(price) { return (\'%s\').replace(\'%s\', price.formatMoney(%d, \'%s\', \'%s\')); }', $selected_currency->val('currency_format'), '%s', $selected_currency->ival('currency_decimals'), $this->selected_language->val('language_decimal_separator'), $this->selected_language->val('language_thousands_separator') );
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
