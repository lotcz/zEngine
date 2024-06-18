<?php

require_once __DIR__ . '/../classes/model.php';

class TrainslatorCacheModel extends zModel {

	public $table_name = 'trainslator_cache';

	public function loadByHash(int $language_id, string $hash) {
		$filter = 'trainslator_cache_language_id = ? and trainslator_cache_key_hash = ?';
		$this->loadSingle($filter, [$language_id, $hash], [PDO::PARAM_INT, PDO::PARAM_STR]);
	}

}
