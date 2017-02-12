<?php

require_once __DIR__ . '/../models/user.m.php';
require_once __DIR__ . '/../models/session.m.php';

class authModule extends zModule {
	
	private $db = null;
		
	public $user = null;
	public $session = null;
		
	function onEnabled() {
		$this->requireConfig();
		$this->requireModule('mysql');
		$this->requireModule('messages');
		$this->db = $this->z->core->db;
		$this->checkAuthentication();
	}

	public function isAuth() {
		return isset($this->user) && isset($this->session);
	}
	
	public function can($perm_name) {
		return $this->isAuth() && ($this->user->val('user_is_superuser') || $this->user->hasPermission($perm_name));
	}
	
	public function login($loginoremail, $password) {
		
		if (isset($_COOKIE[$this->cookie_name])) {
			$this->logout();
		}
		
		$user = new UserModel($this->db);
		$user->loadByLoginOrEmail($loginoremail);
		
		if (isset($user) && $user->is_loaded) {
			if ($user->val('user_failed_attempts') > $this->config['max_attempts']) {
				$this->z->messages->add(t('Max. number of login attempts exceeded. Please ask for new password.'), 'error');
				return false;
			}
			if (Self::verifyPassword($password, $user->val('user_password_hash'))) {
				// success - create new session				
				$this->user = $user;
				$this->updateLastAccess();
				$token = $this->generateToken();
				$token_hash = Self::hashPassword($token);
				$expires = time()+$this->config['session_expire'];
				$session = new UserSessionModel($this->db);
				$session->data['user_session_token_hash'] = $token_hash;
				$session->data['user_session_user_id'] = $this->user->val('user_id');
				$session->data['user_session_expires'] = zSqlQuery::mysqlTimestamp($expires);
				$session->save();
				setcookie($this->config['cookie_name'], $session->val('user_session_id') . "-" . $token, $expires, '/', false, false); 				
				$this->session = $session;
				return true;
			} else {
				$user->data['user_failed_attempts'] += 1;
				$user->save();
				return false;
			}
			
		}
		
	}
	
	public function checkAuthentication() {
		$this->user = null;
						
		if (isset($_COOKIE[$this->config['cookie_name']])) {
			$arr = explode('-', $_COOKIE[$this->config['cookie_name']]);
			$session_id = intval($arr[0]);
			$session_token = $arr[1];
		}
		
		if (isset($session_id)) {
			$this->session = new UserSessionModel($this->db, $session_id);			
			if (isset($this->session) && $this->session->is_loaded && Self::verifyPassword($session_token, $this->session->val('user_session_token_hash'))) {
				$expires = time()+$this->config['session_expire'];
				$session = new UserSessionModel($this->db);
				$session->data['user_session_id'] = $session_id;
				$session->data['user_session_expires'] = zSqlQuery::mysqlTimestamp($expires);
				$session->save();
				setcookie($this->cookie_name, $this->session->val('user_session_id') . '-' . $session_token, $expires, '/', false, false); 				
				$this->user = new UserModel($this->db, $this->session->val('user_session_user_id'));				
				$this->updateLastAccess();
			}
		}
	}
	
	public function updateLastAccess() {
		if ($this->isAuth()) {
			$user = new UserModel($this->db);
			$user->data['user_id'] = $this->user->val('user_id');
			$user->data['user_last_access'] = zSqlQuery::mysqlTimestamp(time());
			$user->save();
		}
	}
	
	public function logout() {
		$this->user = null;
		
		if (isset($_COOKIE[$this->cookie_name])) {			
			unset($_COOKIE[$this->cookie_name]);
			setcookie($this->cookie_name, '', time()-3600, '/', false, false);
		}
		
		if (isset($this->session)) {
			$this->session->deleteById();
			$this->session = null;
		}
	}
	
	private function generateToken() {
		return generateToken(50);
	}

	static function hashPassword($pass) {
		return password_hash($pass, PASSWORD_DEFAULT);
	}
	
	static function verifyPassword($pass, $hash) {
		return password_verify($pass, $hash);
	}
	
}