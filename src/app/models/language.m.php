<?php

class LanguageModel extends zModel {
	
	public $table_name = 'languages';
	public $id_name = 'language_id';
	
	public function formatDecimal($number, $decimals = 2) {
		return number_format($number, $decimals, $this->val('language_decimal_separator'), $this->val('language_thousands_separator'));
	}
	
	public function formatInteger($number) {
		return number_format($number, 0, '', $this->val('language_thousands_separator'));
	}
	
	public function formatDate($date) {
		if (isset($date)) {
			return date($this->val('language_date_format'), $date);
		} else {
			return '';
		}
	}
	
	public function formatDatetime($date) {
		if (isset($date)) {
			return date($this->val('language_datetime_format'), $date);
		} else {
			return '';
		}		
	}
}