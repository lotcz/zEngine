<?php

require_once __DIR__ . '/../app/models/user.m.php';
require_once __DIR__ . '/../app/models/session.m.php';
require_once __DIR__ . '/../app/models/ip_failed.m.php';

/**
* Module that handles authentication for administration area.
*/
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

	/**
	* Return true if administrator is authenticated.
	*/
	public function isAuth() {
		return isset($this->user) && isset($this->session);
	}

	/**
	* Return true if authenticated admin has give permission.
	*/
	public function can($perm_name) {
		return $this->isAuth() && ($this->user->val('user_is_superuser') || $this->user->hasPermission($perm_name));
	}

	/**
	* Perform login for given username/email and password by creating a session. Return true if successful.
	*/
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
				IpFailedAttemptModel::saveFailedAttempt($this->db);
				return false;
			}

		} else {
			return false;
		}
	}

	/**
	* Verifies if there is admin logged in. 
	* Call this only once in the beginning of request processing and then call to isAuth() method
	to check whether admin is authenticated.
	*/
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

	/**
	* Save last access date and time for logged in admin.
	*/
	public function updateLastAccess() {
		if ($this->isAuth()) {
			$user = new UserModel($this->db);
			$user->data['user_id'] = $this->user->val('user_id');
			$user->data['user_last_access'] = zSqlQuery::mysqlTimestamp(time());
			$user->save();
		}
	}

	/**
	* Perform logout operation by deleting current admin's session.
	*/
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
		return z::generateRandomToken(50);
	}

	public function generateResetPasswordToken() {
		return z::generateRandomToken(100);
	}

	static function hashPassword($pass) {
		return z::createHash($pass);
	}

	static function verifyPassword($pass, $hash) {
		return z::verifyHash($pass, $hash);
	}

}
