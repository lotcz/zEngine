<?php

require_once "async-standard-job.php";

abstract class TrainslateAsyncJob extends AsyncStandardJob {

	protected trainslatorModule $trainslator;

	function __construct(
		trainslatorModule $trainslator
	) {
		$this->trainslator = $trainslator;
	}

	public function getJobName(): string {
		return "Trainslate - {$this->getTableName()}";
	}

	public function getStateFieldName(): string {
		return "{$this->getTableName()}_translation_ready";
	}

	public function getDb(): dbModule {
		return $this->trainslator->z->db;
	}

}
