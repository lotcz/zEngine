<?php

class Emails {

	static function sendPlain($from, $to, $cc, $subject, $body) {
		$headers = sprintf('From: %s', $from);
		if (isset($cc) && strlen($cc) > 0) {
			$headers .= "\r\n" . sprintf('CC: %s', $cc);
		}
		mail($to, $subject, $body, $headers);
	}

}