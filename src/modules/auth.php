<?php

require_once __DIR__ . '/../app/models/user.m.php';
require_once __DIR__ . '/../app/models/session.m.php';
require_once __DIR__ . '/../app/models/ip_failed.m.php';

class authModule extends zModule {

	private $db = null;

	public $user = null;
	public $session = null;

	public $cookie_name = 'session_token';

	function onEnabled() {
		$this->requireConfig();
		$this->cookie_name = $this->getConfigValue('cookie_name', $this->cookie_name);
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
		$ip = $_SERVER['REMOTE_ADDR'];

		if (isset($_COOKIE[$this->cookie_name])) {
			$this->logout();
		}

		$user = new UserModel($this->db);
		$user->loadByLoginOrEmail($loginoremail);

		if (isset($user) && $user->is_loaded) {
			if ($user->val('user_failed_attempts') > $this->getConfigValue('max_attempts')) {
				$this->z->messages->add($this->z->core->t('Max. number of login attempts exceeded. Please ask for new password.'), 'error');
				return false;
			}
			if (Self::verifyPassword($password, $user->val('user_password_hash'))) {
				// success - create new session
				$this->user = $user;
				$this->updateLastAccess();
				$token = $this->generatePasswordToken();
				$token_hash = Self::hashPassword($token);
				$expires = time()+$this->config['session_expire'];
				$session = new UserSessionModel($this->db);
				$session->data['user_session_token_hash'] = $token_hash;
				$session->data['user_session_user_id'] = $this->user->val('user_id');
				$session->data['user_session_expires'] = zSqlQuery::mysqlTimestamp($expires);
				$session->data['user_session_ip'] = $ip;
				$session->save();
				setcookie($this->cookie_name, $session->val('user_session_id') . "-" . $token, $expires, '/', false, false);
				$this->session = $session;
				return true;
			} else {
				$user->data['user_failed_attempts'] += 1;
				$user->save();
				IpFailedAttemptModel::saveFailedAttempt($db);
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

	public function generatePasswordToken() {
		return $this->generateRandomToken(50);
	}

	public function generateResetPasswordToken() {
		return $this->generateRandomToken(100);
	}

	static function hashPassword($pass) {
		return password_hash($pass, PASSWORD_DEFAULT);
	}

	static function verifyPassword($pass, $hash) {
		return password_verify($pass, $hash);
	}

	/*
		TOKEN GENERATOR

		example: $token = generateRandomToken(10);
		-- now $token is something like '9HuE48ErZ1'
	*/
	static function getRandomNumber() {
		return rand(0,9);
	}

	static function getRandomLowercase() {
		return chr(rand(97,122));
	}

	static function getRandomUppercase() {
		return strtoupper(Self::getRandomLowercase());
	}

	static function generateRandomToken($len) {
		$s = '';
		for ($i = 0; $i < $len; $i++) {
			$case = rand(0,2);
			if ($case == 0) {
				$s .= Self::getRandomNumber();
			} elseif ($case == 1) {
				$s .= Self::getRandomUppercase();
			} else {
				$s .= Self::getRandomLowercase();
			}
		}
		return $s;
	}

}
