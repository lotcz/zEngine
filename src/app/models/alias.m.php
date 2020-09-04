<?php

require_once __DIR__ . '/../classes/model.php';

/**
 * Maps URL aliases to internal page paths (URL is what is asked for, path is what is displayed)
 */
class AliasModel extends zModel {

	public function loadByUrl($url) {
		$filter = 'alias_url = ?';
		$this->loadSingle($filter, [$url], [PDO::PARAM_STR]);
	}

}
