<?php

require_once __DIR__ . '/../models/user.m.php';
require_once __DIR__ . '/../models/session.m.php';

/**
* Module that handles user authentication.
*/
class authModule extends zModule {

	public array $depends_on = ['resources', 'db', 'i18n', 'cookies', 'messages', 'security'];

	private $authentication_checked = false;

	public $user = null;
	public $session = null;
	public $session_token = null;

	public $cookie_name = 'session_token';

	public $public_login_home = '';

	function onEnabled() {
		$this->requireConfig();
		$this->cookie_name = $this->getConfigValue('cookie_name', $this->cookie_name);
		$this->public_login_home = $this->getConfigValue('public_login_home', $this->public_login_home);
	}

	function onBeforeInit() {
		$this->checkAuthentication();
		$this->z->core->insertJS(
			[
				'z_auth' => [
					'session_token_cookie_name' => $this->cookie_name
				]
			]
		);
	}

	/**
	* Return true if a user is authenticated.
	*/
	public function isAuth() {
		$this->checkAuthentication();
		return isset($this->user) && isset($this->session);
	}

	/**
	* Return true if there is no authenticated user or authenticated user is anonymous.
	*/
	public function isAnonymous() {
		return ((!$this->isAuth()) || $this->user->isAnonymous());
	}

	public function createAnonymousSession() {
		$user = $this->createUser($this->z->core->t('Anonymous'), null, null, null, UserModel::user_state_anonymous);
		$this->createSession($user);
	}

	public function createSession($user) {
		$ip = z::getClientIP();

		// TODO: check if IP address has too many sessions already and if it not banned

		$this->user = $user;
		$this->session_token = $this->generateSessionToken();
		$token_hash = Self::hashPassword($this->session_token);
		$expires = time() + $this->config['session_expire'];
		$session = new UserSessionModel($this->z->db);
		$session->data['user_session_token_hash'] = $token_hash;
		$session->data['user_session_user_id'] = $this->user->ival('user_id');
		$session->data['user_session_expires'] = z::mysqlTimestamp($expires);
		$session->data['user_session_ip'] = $ip;
		$session->save();
		$this->session = $session;
		$this->updateSessionCookie($expires);
		$this->updateLastAccess();
		return $session;
	}

	private function updateSessionCookie($expire) {
		$this->z->cookies->setCookie($this->cookie_name, $this->session->val('user_session_id') . "-" . $this->session_token, $expire);
	}

	private function getSessionCookie() {
		return $this->z->cookies->getCookie($this->cookie_name);
	}

	/**
	* Perform login for given username/email and password by creating a session. Return true if successful.
	*/
	public function login($loginoremail, $password) {
		if ($this->getSessionCookie() != null) {
			$this->logout();
		}

		$user = new UserModel($this->z->db);
		$user->loadByLoginOrEmail($loginoremail);

		if (!(isset($user) && $user->is_loaded)) {
			$this->z->security->saveFailedAttempt();
			return false;
		}

		if ($user->val('user_failed_attempts') > $this->getConfigValue('max_attempts')) {
			$this->z->messages->add($this->z->core->t('Max. number of login attempts exceeded. Please ask for new password.'), 'error');
			return false;
		}

		if (Self::verifyPassword($password, $user->val('user_password_hash'))) {
			// success - create new session
			$this->createSession($user);
			return true;
		} else {
			$user->data['user_failed_attempts'] += 1;
			$user->save();
			$this->z->security->saveFailedAttempt();
			return false;
		}

	}

	/**
	* Verifies if there is a user logged in.
	* Call this only once in the beginning of request processing and then call to isAuth() method to check whether any user is authenticated.
	*/
	private function checkAuthentication() {
		if (!$this->authentication_checked) {
			$this->user = null;

			$cookie_value = $this->getSessionCookie();
			if (isset($cookie_value)) {
				$arr = explode('-', $cookie_value);
				$session_id = intval($arr[0]);
				$this->session_token = $arr[1];
			}

			if (isset($session_id)) {
				$this->session = new UserSessionModel($this->z->db, $session_id);
				if (isset($this->session) && $this->session->is_loaded && Self::verifyPassword($this->session_token, $this->session->val('user_session_token_hash'))) {
					$expires = time() + $this->config['session_expire'];
					$this->setSessionExpiration($expires);
					$this->user = new UserModel($this->z->db, $this->session->val('user_session_user_id'));
					$this->updateLastAccess();
				}
			}

			$this->authentication_checked = true;
		}
	}

	public function loadUserByLoginOrEmail($email) {
		$usr = new UserModel($this->z->db);
		$usr->loadByLoginOrEmail($email);
		return $usr->is_loaded ? $usr : null;
	}

	public function emailExists($email) {
		if ($this->isAuth() && $this->z->auth->user->get('user_email') === $email) {
			return true;
		}
		$usr = $this->loadUserByLoginOrEmail($email);
		return $usr !== null;
	}

	public function obtainAuthenticatedUser() {
		if (!$this->isAuth()) {
			$this->createAnonymousSession();
		}
		return $this->user;
	}

	/**
	* Set current session's expiration date
	*/
	public function setSessionExpiration($expires) {
		UserSessionModel::setSessionExpiration($this->z->db, $this->session->ival('user_session_id'), $expires);
		$this->updateSessionCookie($expires);
	}

	/**
	* Save last access date and time for logged in user.
	*/
	private function updateLastAccess() {
		if (isset($this->user)) {
			$user = new UserModel($this->z->db);
			$user->data['user_id'] = $this->user->ival('user_id');
			$user->data['user_last_access'] = z::mysqlTimestamp(time());
			$user->save();
		}
	}

	/**
	* Perform logout operation by unsetting cookie and deleting current session.
	*/
	public function logout() {
		$this->user = null;

		if ($this->getSessionCookie()) {
			$this->z->cookies->resetCookie($this->cookie_name);
		}

		if (isset($this->session)) {
			$this->session->delete();
			$this->session = null;
		}
	}

	/**
	* Create user account.
	* @return UserModel
	*/
	public function createUser($full_name, $login, $email, $password, $state) {
		$user = new UserModel($this->z->db);
		$user->data['user_name'] = $full_name;
		$user->data['user_login'] = $login;
		$user->data['user_email'] = $email;
		$user->data['user_state'] = $state;
		$user->data['user_password_hash'] = $this->hashPassword($password);
		if ($this->z->isModuleEnabled('i18n') && isset($this->z->i18n->selected_language)) {
			$user->data['user_language_id'] = $this->z->i18n->selected_language->val('language_id');
		} else {
			$user->data['user_language_id'] = 1;
		}
		$user->save();
		return $user;
	}

	/**
	* Create and activates user account. Used for db initialization.
	* @return UserModel
	*/
	public function createActiveUser($full_name, $login, $email, $password) {
		$user = $this->createUser($full_name, $login, $email, $password, UserModel::user_state_active);
		return $user;
	}

	/**
	* Create a user account and send activation email. Used on user registration.
	* @return UserModel
	*/
	public function registerUser($full_name, $login, $email, $password) {
		if ($this->emailExists($email)) {
			throw new Exception("Email $email already exists!");
		}
		if ($this->isAuth() && $this->user->isAnonymous()) {
			$user = $this->user;
			$user->data->set('user_email', $email);
			$user->data->set('user_state', UserModel::user_state_waiting_for_activation);
			$user->save();
		} else {
			$user = $this->createUser($full_name, $login, $email, $password, UserModel::user_state_waiting_for_activation);
		}
		$activation_token = $this->generateAccountActivationToken();
		$user->data['user_reset_password_hash'] = $this->hashPassword($activation_token);
		$expires = time() + $this->getConfigValue('reset_password_expires');
		$user->data['user_reset_password_expires'] = z::mysqlTimestamp($expires);
		$user->save();

		$subject = $this->getEmailSubject($this->z->core->t('Registration'));
		$activation_link = sprintf('%s?email=%s&activation_token=%s', $this->z->core->url('activate'), $email, $activation_token);
		$this->z->emails->renderAndSend($email, $subject, 'registration', ['user' => $user, 'activation_link' => $activation_link]);
		$this->z->messages->success($this->z->core->t('Thank you for your registration on our website.'));
		$this->z->messages->warning($this->z->core->t('An e-mail was sent to your address with account activation instructions.'));

		return $user;
	}

	/* EMAILS */

	public function getEmailSubject($text) {
		return sprintf('%s: %s', $this->z->core->getConfigValue('site_title'), $text);
	}

	static function hashPassword($pass) {
		return z::createHash($pass);
	}

	static function verifyPassword($pass, $hash) {
		return z::verifyHash($pass, $hash);
	}

	public function isValidPassword($password) {
		return (strlen($password) >= $this->getConfigValue('min_password_length', 5));
	}

	public function generatePasswordToken() {
		return z::generateRandomToken(50);
	}

	public function generateResetPasswordToken() {
		return z::generateRandomToken(100);
	}

	public function generateAccountActivationToken() {
		return z::generateRandomToken(100);
	}

	private function generateSessionToken() {
		return z::generateRandomToken(50);
	}

}
