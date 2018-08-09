<?php

class TranslationModel extends zModel {

	public $table_name = 'translations';
	public $id_name = 'translation_id';

	public function load($language_id, $name) {
		$filter = 'translation_language_id = ? and translation_name = ?';
		$this->loadSingle($filter, [$language_id, $name]);
	}

}
