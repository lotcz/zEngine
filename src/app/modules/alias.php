<?php

require_once __DIR__ . '/../models/alias.m.php';

/**
* Module that handles page aliases.
*/
class aliasModule extends zModule {

	public $depends_on = ['db'];

	public function onEnabled() {

	}

	public function OnBeforeInit() {
		$alias = new AliasModel($this->z->db);
		$alias->loadByUrl($this->z->core->raw_path);
		if ($alias->is_loaded) {
			$this->z->core->parseURL($alias->val('alias_path'));
		}
	}

	public function slugify($str) {
		$trimmed = z::trimSpecial($str);
		$transliterated = z::transliterate($trimmed, $this->z->core->default_encoding);
		$cleaned = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $transliterated);
		$lowered = strtolower($cleaned);
		$replaced = preg_replace("/[_| -]+/", '-', $lowered);
		return $replaced;
	}

}
