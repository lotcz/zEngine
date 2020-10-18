<?php

require_once __DIR__ . '/../classes/model.php';

class FormProtectionTokenModel extends zModel {

	public $table_name = 'form_protection_token';

	static function createToken($db, $user_session_id, $ip, $form_name, $token_hash) {
		$token = new FormProtectionTokenModel($db);
		$token->set('form_protection_token_user_session_id', $user_session_id);
		$token->set('form_protection_token_ip', $ip);
		$token->set('form_protection_token_form_name', $form_name);
		$token->set('form_protection_token_hash', $token_hash);
		$token->save();
		return $token;
	}

}
