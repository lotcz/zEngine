<?php

require_once __DIR__ . '/../classes/model.php';

class EmailEvaluationCacheModel extends zModel {

	public $table_name = 'email_evaluation_cache';

	public function loadByHash(string $hash) {
		$filter = 'email_evaluation_cache_hash = ?';
		$this->loadSingle($filter, [$hash], [PDO::PARAM_STR]);
	}

}
