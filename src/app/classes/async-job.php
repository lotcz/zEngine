<?php

abstract class AsyncJob {

	public abstract function getJobName(): string;

	public function getProcessingChunkSize(): int {
		return 1;
	}

	public abstract function getItemsCountWaiting(): int;

	public abstract function getItemsCountProcessing(): int;

	public abstract function loadNextWaiting(): ?zModel;

	/**
	 * These will be requeued
	 */
	public abstract function loadItemsExpired(): array;

	public abstract function markItemReady(zModel $item): void;

	public abstract function markItemProcessing(zModel $item): void;

	public abstract function markItemWaiting(zModel $item): void;

	public abstract function processItem(zModel $item): void;

	public function execute() {
		$expired = $this->loadItemsExpired();
		$expiredCount = count($expired);
		if ($expiredCount > 0) {
			foreach ($expired as $expiredItem) {
				$this->markItemWaiting($expiredItem);
			}
		}

		$max = $this->getProcessingChunkSize();
		$processed = 0;

		$waitingCount = $this->getItemsCountWaiting();
		echo "Waiting: $waitingCount, Chunk size: $max, Expired & requeued: $expiredCount" . PHP_EOL;

		$waiting = $this->loadNextWaiting();
		while ($processed < $max && isset($waiting)) {
			$processed++;
			$this->markItemProcessing($waiting);
			try {
				$this->processItem($waiting);
				$this->markItemReady($waiting);
			} catch (Exception $e) {
				echo "Error when processing async job: " . $e->getMessage() . PHP_EOL;
			}

			$waiting = $this->loadNextWaiting();
		}

		echo "Processed $processed items" . PHP_EOL;

	}
}
