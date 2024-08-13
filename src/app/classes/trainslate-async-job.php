<?php

require_once "async-standard-job.php";

abstract class TrainslateAsyncJob extends AsyncStandardJob {

	private trainslatorModule $trainslator;

	function __construct(
		trainslatorModule $trainslator
	) {
		$this->trainslator = $trainslator;
	}

	public function getJobName(): string {
		return "Trainslate - {$this->getTableName()}";
	}

	public function processItem(zModel $item): void {
		// TODO: Implement processItem() method.
	}

	public function getDb(): dbModule {
		return $this->trainslator->z->db;
	}

	public function getChunkSize(): int	{
		// TODO: Implement getChunkSize() method.
	}


}
