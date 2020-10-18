<?php

require_once __DIR__ . '/../models/alias.m.php';

/**
* Module that handles page aliases.
*/
class aliasModule extends zModule {

	public $depends_on = ['db'];

	public function OnBeforeInit() {
		$alias = new AliasModel($this->z->db);
		$alias->loadByUrl($this->z->core->raw_path);
		if ($alias->is_loaded) {
			$this->z->core->parseURL($alias->val('alias_path'));
		}
	}

	public function slugify($str) {
		$result = z::trimSpecial($str);
		$result = strtolower($result);
		$result = z::transliterate($result, $this->z->core->default_encoding);
		$result = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $result);
		$result = preg_replace("/[_| -]+/", '-', $result);
		return $result;
	}

	/*
		Create alias if given URL alias not exists yet.
	 */
	public function createUrlIfNotExists($url, $path) {
		$alias = new AliasModel($this->z->db);
		$alias->loadByUrl($url);
		if (!$alias->is_loaded) {
			$alias->set('alias_url', $url);
			$alias->set('alias_path', $path);
			$alias->save();
		}
	}

	/*
		Delete all aliases for given path.
	 */
	public function deleteAllForPath($path) {
		$this->z->db->executeDeleteQuery('alias', sprintf('%s = ?', 'alias_path'), [$path], [PDO::PARAM_STR]);
	}
}
