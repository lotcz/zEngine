<?php

class AliasModel extends zModel {

	public $table_name = 'aliases';
	public $id_name = 'alias_id';

	public function loadByUrl($url) {
		$filter = 'alias_url = ?';
		$this->loadSingleFiltered($filter, [$url]);
	}

	public function setUrl($url) {
		if ($url != $this->val('alias_url')) {
			$a = new AliasModel($this->db);
			$url = AliasModel::generateAliasUrl(z::trim($url));
			$new_url = $url;
			$a->loadByUrl($new_url);
			$counter = 0;
			while ($a->is_loaded && $a->val('alias_id') != $this->val('alias_id')) {
				$counter += 1;
				$new_url = $url . '-' . $counter;
				$a->loadByUrl($new_url);
			}
			$this->data['alias_url'] = $new_url;
		}
	}

	static function generateAliasUrl($string) {
		setlocale(LC_ALL, 'cs_CZ.UTF8');
		$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $string);
		$clean = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $clean);
		$clean = strtolower($clean);
		$clean = preg_replace("/[_| -]+/", '-', $clean);
		return $clean;
	}

}
