<?php

require_once __DIR__ . '/../models/trainslator_cache.m.php';

/**
* Module that automatically translates text using chatGPT API
*/
class trainslatorModule extends zModule {

	public $depends_on = ['i18n', 'db', 'chatgpt'];

	public $also_install = [];

	public $cache_hashing_algorithm = 'md5';

	public $zero_language_id = null;

	public $internal_cache = [];

	private $system_prompts = [
		'text' => "You are helpful automatic translator that accurately translate texts to different languages. You always answer with exact translation.",
		'html' => "You are helpful automatic translator that accurately translate web content to different languages. " .
			"You always keep HTML tags structure in place and keep all attribute values so translated content can be safely displayed on web. " .
			"You never remove images or heading tags from original content."
	];

	public function onEnabled() {
		$this->cache_hashing_algorithm = $this->getConfigValue('cache_hashing_algorithm', $this->cache_hashing_algorithm);
		$zero_language_code = $this->getConfigValue('zero_language', 'cs');
		$zero_language = $this->z->i18n->getLanguageByCode($zero_language_code);
		$this->zero_language_id = $zero_language->ival('language_id');
	}

	public function getLanguages() {
		return $this->z->i18n->available_languages;
	}

	private function getLanguage(?int $language_id = null): LanguageModel {
		$language = $this->z->i18n->getLanguageById($language_id);
		if (empty($language)) {
			return $this->z->i18n->getSelectedLanguage();
		}
		return $language;
	}

	public function isZeroLanguage(?int $language_id = null) {
		if (empty($language_id)) {
			$language = $this->getLanguage();
			$language_id = $language->ival('language_id');
		}
		return $language_id == $this->zero_language_id;
	}

	public function getZeroLanguage() {
		return $this->getLanguage($this->zero_language_id);
	}

	/*
	 * INTERNAL CACHE
	*/

	private function internalCacheExists($language_id, $key) {
		if (!isset($this->internal_cache[$language_id])) {
			return false;
		}
		return isset($this->internal_cache[$language_id][$key]);
	}

	private function setInternalCache($language_id, $key, $value) {
		if (!isset($this->internal_cache[$language_id])) {
			$this->internal_cache[$language_id] = [];
		}
		$this->internal_cache[$language_id][$key] = $value;
	}

	private function getInternalCache($language_id, $key) {
		if (!$this->internalCacheExists($language_id, $key)) {
			return null;
		}
		return $this->internal_cache[$language_id][$key];
	}

	/*
	 * DB CACHE
	*/

	public function loadDbCacheStats() {
		$stats = zModel::selectSql(
			$this->z->db,
			"select language_id, language_name as n, count(trainslator_cache_id) as c
					from view_trainslator_cache
					group by language_id, language_name
				");
		$info = [];
		foreach ($stats as $stat) {
			$info[$stat->val('n')] = $stat->val('c');
		}
		return $info;
	}

	public function getCacheKeyHash(string $key): string {
		return hash($this->cache_hashing_algorithm, $key);
	}

	private function loadCacheByHash(int $language_id, string $hash): ?TrainslatorCacheModel {
		$cached = new TrainslatorCacheModel($this->z->db);
		$cached->loadByHash($language_id, $hash);
		return $cached->is_loaded ? $cached : null;
	}

	private function loadCacheByKey(int $language_id, string $key): ?TrainslatorCacheModel {
		return $this->loadCacheByHash($language_id, $this->getCacheKeyHash($key));
	}

	private function loadCacheByHashes(int $language_id, array $hashes): array {
		if (empty($hashes)) return [];
		$sqlList = ['?'];
		$sqlValues = [$language_id];
		$sqlTypes = [PDO::PARAM_INT];
		foreach ($hashes as $hash) {
			$sqlList[] = '?';
			$sqlValues[] = $hash;
			$sqlTypes[] = PDO::PARAM_STR;
		}
		$sql = implode(',', $sqlList);
		return TrainslatorCacheModel::select(
			$this->z->db,
			TrainslatorCacheModel::getTableName(),
			"trainslator_language_id = ? and trainslator_cache_key_hash IN ($sql)",
			null,
			null,
			$sqlValues,
			$sqlTypes
		);
	}

	private function loadCacheByKeys(int $language_id, array $keys): array {
		$hashes = [];
		foreach ($keys as $key) {
			$hashes[] = $this->getCacheKeyHash($key);
		}
		return $this->loadCacheByHashes($language_id, $hashes);
	}

	private function deleteCacheByHash(int $language_id, string $hash): void {
		$this->deleteCacheByHashes($language_id, [$hash]);
	}

	private function deleteCacheByKey(int $language_id, string $key): void {
		$this->deleteCacheByHash($language_id, $this->getCacheKeyHash($key));
	}

	private function deleteCacheByHashes(int $language_id, array $hashes): void {
		if (empty($hashes)) return;
		$sqlList = ['?'];
		$sqlValues = [$language_id];
		$sqlTypes = [PDO::PARAM_INT];
		foreach ($hashes as $hash) {
			$sqlList[] = '?';
			$sqlValues[] = $hash;
			$sqlTypes[] = PDO::PARAM_STR;
		}
		$sql = implode(',', $sqlList);
		$this->z->db->executeDeleteQuery(
			TrainslatorCacheModel::getTableName(),
			"trainslator_language_id = ? and trainslator_cache_key_hash IN ($sql)",
			$sqlValues,
			$sqlTypes
		);
	}

	private function deleteCacheByKeys(int $language_id, array $keys): void {
		$hashes = [];
		foreach ($keys as $key) {
			$hashes[] = $this->getCacheKeyHash($key);
		}
		$this->deleteCacheByHashes($language_id, $hashes);
	}

	/*
	 * TRANSLATE
	 */

	private function performTranslate(string $text, string $language_name, ?string $mode = 'text') {
		$zero_language = $this->getZeroLanguage();
		$zero_language_name = $zero_language->get('language_name');
		return $this->z->chatgpt->ask(
			[
				"Translate from $zero_language_name to $language_name",
				$text
			],
			$this->system_prompts[$mode]
		);
	}

	public function translate(?string $text, ?int $language_id = null) {
		// empty
		if (empty($text)) return $text;

		$language = $this->getLanguage($language_id);
		$language_id = $language->ival('language_id');

		// zero lang
		if ($this->isZeroLanguage($language_id)) return $text;

		// core translation
		if ($this->z->i18n->isSelectedLanguage($language_id)) {
			if ($this->z->i18n->translationExists($text)) return $this->z->i18n->translate($text);
		}

		// internal cache
		$hash = $this->getCacheKeyHash($text);
		$icache = $this->getInternalCache($language_id, $hash);
		if (!empty($icache)) return $icache;

		// db cache
		$cached = $this->loadCacheByHash($language_id, $hash);
		if (isset($cached)) {
			$t = $cached->val('trainslator_cache_value');
			$this->setInternalCache($language_id, $hash, $t);
			return $t;
		}

		// perform AI translate
		$translated = $this->performTranslate($text, $language->val('language_name'), z::containsHtmlTags($text) ? 'html' : 'text');

		// save to db cache
		$cached = new TrainslatorCacheModel($this->z->db);
		$cached->set('trainslator_cache_key', $text);
		$cached->set('trainslator_cache_key_hash', $hash);
		$cached->set('trainslator_cache_language_id', $language_id);
		$cached->set('trainslator_cache_value', $translated);
		$cached->save();

		$this->setInternalCache($language_id, $hash, $translated);
		return $translated;
	}

}
