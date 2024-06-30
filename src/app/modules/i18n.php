<?php

require_once __DIR__ . '/../models/language.m.php';
require_once __DIR__ . '/../models/currency.m.php';

/**
* Module that handles application internationalization
* Things like string translation and number and date formatting are handled by this module.
*/
class i18nModule extends zModule {

	public $depends_on = ['cookies', 'resources', 'db'];
	public $also_install = [];

	public $language_cookie_name = 'language';
	public $currency_cookie_name = 'currency';
	public $language_data = null;
	public $available_languages = null;
	public $selected_language = null;
	public $available_currencies = null;
	public $selected_currency = null;

	public function onEnabled() {
		$this->language_cookie_name = $this->getConfigValue('language_cookie_name', $this->language_cookie_name);
		$this->currency_cookie_name = $this->getConfigValue('currency_cookie_name', $this->currency_cookie_name);
		$this->available_currencies = CurrencyModel::all($this->z->db);
		$this->available_languages = LanguageModel::all($this->z->db);
	}

	public function onBeforeInit() {

		if (!$this->getConfigValue('force_default_language')) {

			// first, use currency and language from cookies, if available
			if (isset($_COOKIE[$this->currency_cookie_name])) {
				$this->selectCurrencyByID($_COOKIE[$this->currency_cookie_name]);
			}
			if (isset($_COOKIE[$this->language_cookie_name])) {
				$this->selectLanguageByID($_COOKIE[$this->language_cookie_name]);
			}

			if ($this->z->isModuleEnabled('auth') && $this->z->auth->isAuth()) {
				// update customer default language if different from cookie values
				//todo: fix problem that this prevents changing language in user profile
				if (isset($this->selected_language)) {
					if ($this->z->auth->user->ival('user_language_id') != $this->selected_language->ival('language_id')) {
						$this->z->auth->user->set('user_language_id', $this->selected_language->ival('language_id'));
						$this->z->auth->user->save();
					}
				} else {
					// use saved customer defaults otherwise
					$this->selectLanguageByID($this->z->auth->user->ival('user_language_id'));
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

		// include necessary JS
		$this->z->core->includeJS('resources/i18n.js');
		$this->z->core->insertJS(
			[
				'z_i18n' => [
					'language_cookie_name' => $this->language_cookie_name,
					'currency_cookie_name' => $this->currency_cookie_name,
					'selected_language' => $this->selected_language->data,
					'selected_currency' => $this->selected_currency->data
				]
			]
		);

	}

	public function getSelectedLanguage() {
		return $this->selected_language;
	}

	public function getSelectedLanguageId() {
		return $this->selected_language->val('language_id');
	}

	public function getSelectedLanguageCode() {
		return $this->selected_language->val('language_code');
	}

	public function getSelectedLanguageName() {
		return $this->selected_language->val('language_name');
	}

	public function getLanguageById($language_id) {
		return zModel::find($this->available_languages, 'language_id', $language_id);
	}

	public function getLanguageByCode($language_code) {
		return zModel::find($this->available_languages, 'language_code', $language_code);
	}

	public function selectLanguageById($language_id) {
		$this->selectLanguage($this->getLanguageById($language_id));
	}

	public function selectLanguageByCode($language_code) {
		$this->selectLanguage($this->getLanguageByCode($language_code));
	}

	public function selectCurrency($currency) {
		$this->selected_currency = $currency;
	}

	public function selectCurrencyByID($currency_id) {
		$this->selectCurrency(zModel::find($this->available_currencies, 'currency_id', $currency_id));
	}

	public function loadLanguageData($lang_code) {

		// zEngine localization
		$z_lang_data = [];
		$file_path = __DIR__ . '/../lang/' . $lang_code . '.php';
		if (file_exists($file_path)) {
			$z_lang_data = include $file_path;
		}

		// app localization
		$app_lang_data = [];
		$file_path = $this->z->app_dir . 'lang/' . $lang_code . '.php';

		if (file_exists($file_path)) {
			$app_lang_data = include $file_path;
		}

    	return array_merge($z_lang_data, $app_lang_data);
	}

	public function loadLanguage($lang_code) {
		$this->language_data = $this->loadLanguageData($lang_code);
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

	// return true if static string translation exists in lang files for current language
	public function translationExists($s) {
		return isset($this->language_data) and isset($this->language_data[$s]);
	}

	// directly translates string if translation exists
	// if you want to process tokens use 'core->t()' function instead
	public function translate($s) {
		if ($this->translationExists($s)) {
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

}
