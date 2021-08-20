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
		return z::slugify($str, $this->z->core->default_encoding);
	}

	public function insertAlias($url, $path) {
		$alias = new AliasModel($this->z->db);
		$alias->set('alias_url', $url);
		$alias->set('alias_path', $path);
		$alias->save();
		return $alias;
	}

	/*
		Create alias if doesn't exist yet.
		If different URL is specified for the same path, it is kept active.
		If URL already exists for different path, then new unique URL is created.
	 */
	public function createAlias($url, $path) {
		$alias = new AliasModel($this->z->db);
		$alias->loadByUrl($url);

		if (!$alias->is_loaded) {
			return $this->insertAlias($url, $path);
		}

		if ($alias->val('alias_path') == $path) {
			return $alias;
		}

		$i = 1;
		$unique_url = "";
		while ($alias->is_loaded) {
			$unique_url = "$url-$i";
			$alias->loadByUrl($unique_url);
			$i++;
		}

		return $this->insertAlias($unique_url, $path);
	}

	/*
		Delete all aliases for given path.
	 */
	public function deleteAllForPath($path) {
		$this->z->db->executeDeleteQuery('alias', sprintf('%s = ?', 'alias_path'), [$path], [PDO::PARAM_STR]);
	}

}
