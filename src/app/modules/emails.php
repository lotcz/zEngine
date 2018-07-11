<?php

/**
* Module that handles sending of emails.
*/
class emailsModule extends zModule {

	public function renderAndSend($to, $subject, $template_name, $data, $from = null) {
		$email_body = $this->z->emails->renderEmailBody($template_name, $data);
		$this->sendHTML($to, $subject, $email_body, $from);
	}

	public function sendPlain($to, $subject, $body, $from = null) {
		$this->sendEmail($to, $subject, $body, 'text/plain', $from);
	}

	public function sendHTML($to, $subject, $body, $from = null) {
		$this->sendEmail($to, $subject, $body, 'text/html', $from);
	}

	public function sendEmail($to, $subject, $body, $content_type, $from = null) {
		if ($from == null) {
			$from = $this->z->emails->getConfigValue('from_address');
		}
		$headers = "From: $from;\r\n";
		$headers .= "Content-Type: $content_type;charset=utf-8\r\n";
		mail($to, $subject, $body, $headers);
	}

	public function renderEmailBody($template_name, $email_data) {
		$master_template_path = $this->z->core->app_dir . 'views/email/master.v.php';
		$template_path = $this->z->core->app_dir . 'views/email/' .  $template_name . '.v.php';
		$data = $email_data;
		ob_start();
		include $template_path;
		$body = ob_get_clean();
		include $master_template_path;
		$master = ob_get_clean();
		return $master;
	}
}
