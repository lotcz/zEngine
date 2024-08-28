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

	public abstract function getExpiration(): DateInterval;

	public abstract function getTableName(): string;

	public function getJobName(): string {
		return "Async Standard Job - {$this->getTableName()}";
	}

	public abstract function getStateFieldName(): string;

	public function getIdFieldName(): string {
		return "{$this->getTableName()}_id";
	}

	public function getStateChangedFieldName(): string {
		return "{$this->getStateFieldName()}_changed";
	}

	protected function getAdditionalFilterSql(): ?string {
		return null;
	}

	protected function getStateValueSql(?bool $state): ?string {
		return ($state === null) ? 'null' : ($state ? '1' : '0');
	}

	protected function getStateSql(?bool $state): string {
		if ($state === null) {
			return "{$this->getStateFieldName()} is null";
		}
		return sprintf('%s = %s', $this->getStateFieldName(), $this->getStateValueSql($state));
	}

	protected function getFilterSql(?bool $state): ?string {
		$stateSql = $this->getStateSql($state);
		$additional = $this->getAdditionalFilterSql();
		return (empty($additional)) ? $stateSql : "($stateSql) and ($additional)";
	}

	protected function getItemsCount(?bool $state): int {
		return $this->getDb()->getRecordCount($this->getTableName(), $this->getFilterSql($state));
	}

	public function getItemsCountWaiting(): int {
		return $this->getItemsCount(self::ITEM_STATE_WAITING);
	}

	public function getItemsCountProcessing(): int {
		return $this->getItemsCount(self::ITEM_STATE_PROCESSING);
	}

	public function loadNextWaiting(): ?zModel {
		$items = zModel::select(
			$this->getDb(),
			$this->getTableName(),
			$this->getFilterSql(self::ITEM_STATE_WAITING),
			$this->getStateChangedFieldName(),
			1
		);
		if (count($items) > 0) return $items[0];
		return null;
	}

	public function loadItemsExpired(): array {
		$filterSql = $this->getFilterSql(self::ITEM_STATE_PROCESSING);
		$threshold = (new DateTime())->sub($this->getExpiration());
		$sql = "($filterSql) and {$this->getStateChangedFieldName()} < ?";
		return zModel::select(
			$this->getDb(),
			$this->getTableName(),
			$sql,
			null,
			null,
			[z::mysqlDatetime($threshold)],
			[PDO::PARAM_STR]
		);
	}

	protected function markItem(zModel $item, ?bool $state): void {
		$this->getDb()->executeUpdateQuery(
			$this->getTableName(),
			[$this->getStateFieldName(), $this->getStateChangedFieldName()],
			sprintf('%s = ?', $this->getIdFieldName()),
			[$this->getStateValueSql($state), z::mysqlDatetime(new DateTime()), $item->ival($this->getIdFieldName())],
			[PDO::PARAM_INT, PDO::PARAM_STR, PDO::PARAM_INT]
		);
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
