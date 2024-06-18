<?php

require_once __DIR__ . '/../models/trainslator_cache.m.php';

/**
* Module that automatically translates text using chatGPT API
*/
class trainslatorModule extends zModule {

	public $depends_on = ['i18n', 'db', 'chatgpt'];

	public $also_install = [];

	public $cache_hashing_algorithm = 'md5';

	public $zero_language_id = 1;

	private $system_prompts = [
		'text' => "You are helpful automatic translator that accurately translate texts to different languages.",
		'html' => "You are helpful automatic translator that accurately translate web content to different languages. You always keep HTML tags structure in place and keep all attribute values so translated content can be safely displayed on web."
	];

	public function onEnabled() {
		$this->cache_hashing_algorithm = $this->getConfigValue('cache_hashing_algorithm', $this->cache_hashing_algorithm);
		$this->zero_language_id = $this->getConfigValue('zero_language_id', $this->zero_language_id);
	}

	private function getLanguage(?int $language_id): LanguageModel {
		if (empty($language_id)) {
			return $this->z->i18n->getSelectedLanguage();
		}
		$language = $this->z->i18n->getLanguageById($language_id);
		if (empty($language)) {
			return $this->z->i18n->getSelectedLanguage();
		}
		return $language;
	}

	private function getCacheKeyHash(string $key): string {
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

	private function updateCacheValue(int $language_id, string $key, string $value) {
		$hash = $this->getCacheKeyHash($key);
		$cached = $this->loadCacheByHash($language_id, $hash);
		if (!$cached) {
			$cached = new TrainslatorCacheModel($this->z->db);
			$cached->set('trainslator_cache_key', $key);
			$cached->set('trainslator_cache_key_hash', $hash);
			$cached->set('trainslator_cache_language_id', $language_id);
		}
		$cached->set('trainslator_cache_value', $value);
		$cached->save();
	}

	private function updateCacheValues(int $language_id, array $values) {
		foreach ($values as $key => $value) {
			$this->updateCacheValue($language_id, $key, $value);
		}
	}

	public function translate(string $text, string $language_name, ?string $mode = 'text') {
		return $this->z->chatgpt->ask(
			[
				"Translate to $language_name",
				$text
			],
			$this->system_prompts[$mode]
		);
	}

	public function translateHTML(string $html, string $language_name) {
		return $this->translate($html, $language_name, 'html');
	}

	public function getTranslation(string $text, ?int $language_id = null, ?string $mode = 'text') {
		$language = $this->getLanguage($language_id);
		$language_id = $language->ival('language_id');
		if ($language_id == $this->zero_language_id) return $text;
		$cached = $this->loadCacheByKey($language_id, $text);
		if (isset($cached)) return $cached->val('trainslator_cache_value');
		$translated = $this->translate($text, $language->val('language_name'), $mode);
		$this->updateCacheValue($language_id, $text, $translated);
		return $translated;
	}

	public function getHTMLTranslation(string $html, ?int $language_id = null) {
		return $this->getTranslation($html, $language_id, 'html');
	}

}
