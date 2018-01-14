<?php

class FormXSRFTokenModel extends zModel {

	public $table_name = 'form_xsrf_tokens';
	public $id_name = 'form_xsrf_token_id';
	
	static function createToken($db, $customer_session_id, $user_session_id, $ip, $form_name, $token_hash, $expires) {
		$token = new FormXSRFTokenModel($db);
		$token->set('form_xsrf_token_user_session_id', $user_session_id);
		$token->set('form_xsrf_token_customer_session_id', $customer_session_id);
		$token->set('form_xsrf_token_ip', $ip);
		$token->set('form_xsrf_token_form_name', $form_name);
		$token->set('form_xsrf_token_hash', $token_hash);
		$token->set('form_xsrf_token_expires', zSqlQuery::mysqlDatetime($expires));
		$token->save();
		return $token;
	}
	
	static function verifyToken($db, $token_id, $customer_session_id, $user_session_id, $ip, $form_name, $token_value) {
		$token_verified = false;
		$token = new FormXSRFTokenModel($db, $token_id);
		if ($token->is_loaded) {
			$session_ok = false;
			if (isset($customer_session_id)) {
				$session_ok = ($token->ival('form_xsrf_token_customer_session_id') == $customer_session_id);
			} else {
				$session_ok = ($token->ival('form_xsrf_token_user_session_id') == $user_session_id);
			}
			$token_attrs_ok = ($token->val('form_xsrf_token_ip') == $ip) && ($token->val('form_xsrf_token_form_name') == $form_name);
			$token_hash_ok = z::verifyHash($token_value, $token->val('form_xsrf_token_hash'));
			$token_not_expired = ($token->dtval('form_xsrf_token_expires') > time());
			$token_verified = $session_ok && $token_attrs_ok && $token_hash_ok && $token_not_expired;
			if ($token_verified) {
				$token->deleteByID();
			}
		} else {
			$token_verified = false;
		}		
		return $token_verified;
	}
	
}
