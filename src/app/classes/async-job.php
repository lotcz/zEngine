<?php

abstract class AsyncJob {

	public abstract function getJobName(): string;

	public abstract function getItemsCountWaiting(): int;

	public abstract function getItemsCountProcessing(): int;

	public abstract function loadItemsWaiting(): array;

	public abstract function loadItemsExpired(): array;

	public abstract function markItemReady(zModel $item): void;

	public abstract function markItemProcessing(zModel $item): void;

	public abstract function markItemWaiting(zModel $item): void;

	public abstract function processItem(zModel $item): void;

	public function execute() {
		$expired = $this->loadItemsExpired();
		$expiredCount = count($expired);
		if ($expiredCount > 0) {
			echo "Requeuing $expiredCount expired async jobs" . PHP_EOL;
			foreach ($expired as $expiredItem) {
				$this->markItemWaiting($expiredItem);
			}
		}

		$waiting = $this->loadItemsWaiting();
		$waitingCount = count($waiting);

		if ($waitingCount > 0) {
			echo "Starting processing of $waitingCount waiting async jobs" . PHP_EOL;
			foreach ($waiting as $waitingItem) {
				$this->markItemProcessing($waitingItem);
			}

			foreach ($waiting as $waitingItem) {
				$this->processItem($waitingItem);
				$this->markItemReady($waitingItem);
			}
		} else {
			echo "0 waiting async jobs" . PHP_EOL;
		}

	}
}
