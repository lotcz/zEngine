<?php

require_once "async-standard-job.php";

class SendEmailAsyncJob extends AsyncJob {

	private emailsModule $emails;

	function __construct(emailsModule $emails) {
		$this->emails = $emails;
	}

	public function getJobName(): string {
		return "Rozesílání e-mailů";
	}

	public function getProcessingChunkSize(): int {
		return $this->emails->getConfigValue('limit_emails_per_cron', 4);
	}

	public function getItemsCountWaiting(): int	{
		return $this->emails->getUnsentEmailsCount();
	}

	public function getItemsCountProcessing(): int {
		return $this->emails->getProcessingEmailsCount();
	}

	public function loadNextWaiting(): ?zModel {
		return $this->emails->loadNextUnsentEmail();
	}

	public function loadItemsExpired(): array {
		return $this->emails->loadExpiredUnsentEmails();
	}

	public function markItemReady(zModel $email): void {
		$email->set('email_sent', AsyncStandardJob::ITEM_STATE_READY);
		$email->save();
	}

	public function markItemProcessing(zModel $email): void {
		$email->set('email_sent', AsyncStandardJob::ITEM_STATE_PROCESSING);
		$email->set('email_send_date', z::mysqlDatetime((new DateTime())->getTimestamp()));
		$email->save();
	}

	public function markItemWaiting(zModel $email): void {
		$email->set('email_sent', AsyncStandardJob::ITEM_STATE_WAITING);
		$email->save();
	}

	public function processItem(zModel $email): void {
		$to = $email->val('email_to');
		$this->emails->sendEmail(
			$to,
			$email->val('email_subject'),
			$email->val('email_body'),
			$email->val('email_content_type'),
			$email->val('email_from')
		);
		echo "Sent email to $to" . PHP_EOL;
	}

}
