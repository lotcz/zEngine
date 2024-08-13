<?php

require_once "async-job.php";

/**
 * Performs async jobs in small chunks.
 */
abstract class AsyncStandardJob extends AsyncJob {

	public const ITEM_STATE_READY = 1;

	public const ITEM_STATE_PROCESSING = null;

	public const ITEM_STATE_WAITING = 0;

	public abstract function getDb(): dbModule;

	public abstract function getChunkSize(): int;

	public abstract function getExpiration(): DateInterval;

	public abstract function getTableName(): string;

	public function getJobName(): string {
		return "Async Standard Job - {$this->getTableName()}";
	}

	public abstract function getStateFieldName(): string;

	public function getStateChangedFieldName(): string {
		return "{$this->getStateFieldName()}_changed";
	}

	protected function getStateSql(?bool $state): string {
		if ($state === null) {
			return "{$this->getStateFieldName()} is null";
		}
		return "{$this->getStateFieldName()} = $state";
	}

	protected function loadItems(?bool $state, ?int $limit): array {
		return zModel::select($this->getDb(), $this->getTableName(), $this->getStateSql($state), null, $limit);
	}

	protected function getItemsCount(?bool $state): int {
		return $this->getDb()->getRecordCount($this->getDb(), $this->getStateSql($state));
	}

	public function getItemsCountWaiting(): int {
		return $this->getItemsCount(self::ITEM_STATE_WAITING);
	}

	public function getItemsCountProcessing(): int {
		return $this->getItemsCount(self::ITEM_STATE_PROCESSING);
	}

	public function loadItemsWaiting(): array {
		return $this->loadItems(self::ITEM_STATE_WAITING, $this->getChunkSize());
	}

	public function loadItemsExpired(): array {
		$stateSql = $this->getStateSql(self::ITEM_STATE_PROCESSING);
		$threshold = (new DateTime())->sub($this->getExpiration());
		$sql = "($stateSql) and {$this->getStateChangedFieldName()} < ?";
		return zModel::select(
			$this->getDb(),
			$this->getTableName(),
			$sql,
			null,
			null,
			[$threshold],
			[PDO::PARAM_STR]
		);
	}

	protected function markItem(zModel $item, ?bool $state): void {
		$item->set($this->getStateFieldName(), $state);
		$item->set($this->getStateChangedFieldName(), new DateTime());
		$item->save();
	}

	public function markItemReady(zModel $item): void {
		$this->markItem($item,self::ITEM_STATE_READY);
	}

	public function markItemProcessing(zModel $item): void {
		$this->markItem($item, self::ITEM_STATE_PROCESSING);
	}

	public function markItemWaiting(zModel $item): void {
		$this->markItem($item, self::ITEM_STATE_WAITING);
	}

}
