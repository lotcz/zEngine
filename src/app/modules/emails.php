<?php

require_once __DIR__ . '/../models/email.m.php';
require_once __DIR__ . '/../classes/send-email-async-job.php';

/**
* Module that handles sending of emails.
*/
class emailsModule extends zModule {

	public function sendEmail($to, $subject, $body, $content_type, $from = null, $reply_to = null) {
		if ($from == null) {
			$from = $this->getConfigValue('from_address');
		}
		$headers = [];
		$headers[] = "From: $from";
		if ($reply_to !== null) {
			$headers[] = "Reply-to: $reply_to";
		}
		$headers[] = "Content-Type: $content_type;charset=utf-8";

		$origin = ($reply_to === null) ? $from : $reply_to;
		mail(
			$to,
			emailsModule::encodeEmailSubject($subject),
			$body,
			implode("\r\n", $headers),
			"-f$origin"
		);
	}

	public function sendPlain($to, $subject, $body, $from = null) {
		$this->sendEmail($to, $subject, $body, 'text/plain', $from);
	}

	public function sendHTML($to, $subject, $body, $from = null) {
		$this->sendEmail($to, $subject, $body, 'text/html', $from);
	}

	public function renderAndSend($to, $subject, $template_name, $data, $from = null) {
		$email_body = $this->renderEmailBody($template_name, $data);
		$this->sendHTML($to, $subject, $email_body, $from);
	}

	public function renderEmailBody($template_name, $email_data, $master_template_name = 'master') {
		$master_template_path = $this->z->core->app_dir . 'views/email/' . $master_template_name . '.v.php';
		if (!file_exists($master_template_path)) {
			$master_template_path = __DIR__ . '/../views/email/' . $master_template_name . '.v.php';
		}
		$template_path = $this->z->core->app_dir . 'views/email/' .  $template_name . '.v.php';
		if (!file_exists($template_path)) {
			$template_path = __DIR__ . '/../views/email/' .  $template_name . '.v.php';
		}
		$data = $email_data;
		ob_start();
		include $template_path;
		$body = ob_get_clean();
		include $master_template_path;
		$master = ob_get_clean();
		return $master;
	}

	public function getUnsentEmailsCount() {
		return $this->z->db->getRecordCount('email', 'email_sent = 0 and email_send_date <= CURRENT_TIMESTAMP()');
	}

	public function getProcessingEmailsCount() {
		return $this->z->db->getRecordCount('email', 'email_sent IS NULL and email_send_date <= CURRENT_TIMESTAMP()');
	}

	public function loadUnsentEmails() {
		$limit = $this->getConfigValue('limit_emails_per_cron', 4);
		return EmailModel::select($this->z->db, 'email', 'email_sent = 0 and email_send_date <= CURRENT_TIMESTAMP()', 'email_send_date', "0,$limit");
	}

	public function loadExpiredUnsentEmails() {
		$threshold = (new DateTime())->sub(new DateInterval("PT3M"));
		return EmailModel::select(
			$this->z->db,
			'email',
			'email_sent IS NULL and email_send_date <= ?',
			'email_send_date',
			null,
			[z::mysqlDatetime($threshold->getTimestamp())],
			[PDO::PARAM_STR]
		);
	}

	public function addEmailToQueue($to, $subject, $content_type, $body, $from = null) {
		if ($from == null) {
			$from = $this->getConfigValue('from_address');
		}
		$email = new EmailModel($this->z->db);
		$email->set('email_to', $to);
		$email->set('email_from', $from);
		$email->set('email_subject', $subject);
		$email->set('email_content_type', $content_type);
		$email->set('email_body', $body);
		$email->save();
		return $email;
	}

	static function encodeEmailSubject($subject) {
		return '=?utf-8?B?' . base64_encode($subject) . '?=';
	}

	public function cleanSentEmails($days = null) {
		if (empty($days)) {
			$days = $this->getConfigValue('keep_sent_emails_days', 30);
		}
		if ($days <= 0) {
			$this->z->errorlog->write("Number of days to keep old emails is not defined or is zero. Not deleting anything.");
			return 0;
		}
		$now = new \DateTime();
		$interval = \DateInterval::createFromDateString(sprintf('%d days', $days));
		$date = $now->sub($interval);
		$mysqlTimestamp = z::mysqlTimestamp($date->getTimestamp());
		return $this->z->db
			->executeDeleteQuery('email', 'email_sent = 1 and email_send_date <= ?', [$mysqlTimestamp], [PDO::PARAM_STR])
			->rowCount();
	}

	public function getSendEmailsAsyncJob(): SendEmailAsyncJob {
		return new SendEmailAsyncJob($this);
	}

}
