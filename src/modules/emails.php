<?php

class emailsModule extends zModule {

	public function sendPlain($from, $to, $cc, $subject, $body) {
		$headers = sprintf('From: %s', $from);
		if (isset($cc) && strlen($cc) > 0) {
			$headers .= "\r\n" . sprintf('CC: %s', $cc);
		}
		mail($to, $subject, $body, $headers);
	}

	public function sendHTML($from, $to, $cc, $subject, $body) {
		$headers = sprintf('From: %s', $from) . "\r\n";
		$headers .= "Content-Type: text/html;\r\n";
		if (isset($cc) && strlen($cc) > 0) {
			$headers .= sprintf('CC: %s', $cc) . "\r\n";
		}
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