<?php

require_once __DIR__ . '/../models/email.m.php';

/**
* Module that handles sending of emails.
*/
class emailsModule extends zModule {

	private function sendEmail($to, $subject, $body, $content_type, $from = null, $reply_to = null) {
		if ($from == null) {
			$from = $this->z->emails->getConfigValue('from_address');
		}
		$headers = [];
		$headers[] = "From: $from";
		if ($reply_to !== null) {
			$headers[] = "Reply-to: $reply_to";
		}
		$headers[] = "Content-Type: $content_type;charset=utf-8";

		$origin = ($reply_to === null) ? $from : $reply_to;
		mail($to, $subject, $body, implode("\r\n", $headers), "-f$origin");
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

	public function loadUnsentEmails() {
		return EmailModel::select($this->z->db, 'email', 'email_sent = 0 and email_send_date <= CURRENT_TIMESTAMP()', 'email_send_date', '0,100');
	}

	public function processQueue() {
		$total = 0;
		$unsent = $this->loadUnsentEmails();
		while (count($unsent) > 0) {
			$total += count($unsent);
			foreach($unsent as $email) {
				$this->sendEmail($email->val('email_to'), $email->val('email_subject'), $email->val('email_body'), $email->val('email_content_type'));
				$email->set('email_sent', 1);
				$email->save();
			}
			$unsent = $this->loadUnsentEmails();
		}
		return $total;
	}

	public function addEmailToQueue($to, $subject, $content_type, $body, $from = null) {
		if ($from == null) {
			$from = $this->z->emails->getConfigValue('from_address');
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

}
