<?php

class qrModule extends zModule {

	private $base_url = 'http://localhost:8090/';

	private $api_key = 'secretAPIKey';

	public function onEnabled() {
		$this->base_url = $this->getConfigValue('base_url', $this->base_url);
		$this->api_key = $this->getConfigValue('api_key', $this->api_key);
	}

	function getQrUrlTransaction(
		string $target_account_iban,
		string $message,
		int $amount_czk,
		string $recipient_name,
		bool $is_instant,
		?int $variable_symbol
	): string {
		$instant = $is_instant ? "1" : "0";
		$amount = (string)$amount_czk;
		$vs = (string)$variable_symbol;
		$raw = $this->api_key . $target_account_iban . $amount . $message. $recipient_name . $instant . $vs;
		$hash = md5($raw);
		$message = urlencode($message);
		$target_account_iban = urlencode($target_account_iban);
		$recipient_name = urlencode($recipient_name);
		$query = "?secure_token=$hash&target_account=$target_account_iban&message=$message&amount=$amount&recipient_name=$recipient_name&is_instant=$instant&variable_symbol=$vs";
		return z::getUrl($this->base_url, "bank-transfer-secured") . $query;
	}

}
