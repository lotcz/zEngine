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
		string|int|null $variable_symbol,
		int $border = 0,
		string $file_ext = 'svg'
	): string {
		$instant = $is_instant ? "1" : "0";
		$amount = (string)$amount_czk;
		$vs = $variable_symbol ? str_pad((string)$variable_symbol, 10, "0", STR_PAD_LEFT) : "";
		$raw = $this->api_key . $target_account_iban . $amount . $message. $recipient_name . $instant . $vs . $border . $file_ext;
		$hash = md5($raw);
		$message = urlencode($message);
		$target_account_iban = urlencode($target_account_iban);
		$recipient_name = urlencode($recipient_name);
		$query = "?secure_token=$hash&target_account=$target_account_iban&message=$message&amount=$amount&recipient_name=$recipient_name&is_instant=$instant&variable_symbol=$vs&border=$border&file_ext=$file_ext";
		return z::getUrl($this->base_url, "bank-transfer-secured") . $query;
	}

}
