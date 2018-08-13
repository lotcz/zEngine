<?php

require_once __DIR__ . '/../models/user.m.php';
require_once __DIR__ . '/../models/session.m.php';
require_once __DIR__ . '/../models/ip_failed.m.php';

/**
* Module that handles authentication for administration area.
*/
class authModule extends zModule {

	public $depends_on = ['db', 'cookies', 'messages'];

	private $authentication_checked = false;

	public $user = null;
	public $session = null;

	public $cookie_name = 'session_token';

	function onEnabled() {
		$this->requireConfig();
		$this->cookie_name = $this->getConfigValue('cookie_name', $this->cookie_name);
	}

	function OnBeforeInit() {
		$this->checkAuthentication();
	}

	/**
	* Return true if administrator is authenticated.
	*/
	public function isAuth() {
		$this->checkAuthentication();
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

		$user = new UserModel($this->z->db);
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
				$session = new UserSessionModel($this->z->db);
				$session->data['user_session_token_hash'] = $token_hash;
				$session->data['user_session_user_id'] = $this->user->val('user_id');
				$session->data['user_session_expires'] = z::mysqlTimestamp($expires);
				$session->data['user_session_ip'] = $ip;
				$session->save();
				setcookie($this->cookie_name, $session->val('user_session_id') . "-" . $token, $expires, '/', false, false);
				$this->session = $session;
				return true;
			} else {
				$user->data['user_failed_attempts'] += 1;
				$user->save();
				IpFailedAttemptModel::saveFailedAttempt($this->z->db);
				return false;
			}

		} else {
			return false;
		}
	}

	/**
	* Verifies if there is admin logged in.
	* Call this only once in the beginning of request processing and then call to isAuth() method
	* to check whether admin is authenticated.
	*/
	private function checkAuthentication() {
		if (!$this->authentication_checked) {
			$this->user = null;

			if (isset($_COOKIE[$this->config['cookie_name']])) {
				$arr = explode('-', $_COOKIE[$this->config['cookie_name']]);
				$session_id = intval($arr[0]);
				$session_token = $arr[1];
			}

			if (isset($session_id)) {
				$this->session = new UserSessionModel($this->z->db, $session_id);
				if (isset($this->session) && $this->session->is_loaded && Self::verifyPassword($session_token, $this->session->val('user_session_token_hash'))) {
					$expires = time()+$this->config['session_expire'];
					$session = new UserSessionModel($this->z->db);
					$session->data['user_session_id'] = $session_id;
					$session->data['user_session_expires'] = z::mysqlTimestamp($expires);
					$session->save();
					setcookie($this->cookie_name, $this->session->val('user_session_id') . '-' . $session_token, $expires, '/', false, false);
					$this->user = new UserModel($this->z->db, $this->session->val('user_session_user_id'));
					$this->updateLastAccess();
				}
			}

			$this->authentication_checked = true;
		}
	}

	/**
	* Save last access date and time for logged in admin.
	*/
	private function updateLastAccess() {
		if (isset($this->user)) {
			$user = new UserModel($this->z->db);
			$user->data['user_id'] = $this->user->val('user_id');
			$user->data['user_last_access'] = z::mysqlTimestamp(time());
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

	/**
	* Create and activates user account. Used for db initialization.
	*/
	public function createUserAccount($email, $password, $state) {
		$user = new UserModel($this->z->db);
		$user->data['customer_name'] = $full_name;
		$customer->data['customer_email'] = $email;
		$customer->data['customer_state'] = CustomerModel::customer_state_waiting_for_activation;
		$customer->data['customer_language_id'] = $this->z->i18n->selected_language->val('language_id');
		$customer->data['customer_currency_id'] = $this->z->i18n->selected_currency->val('currency_id');
		$customer->data['customer_password_hash'] = $this->z->custauth->hashPassword($password);
		$activation_token = $this->z->custauth->generateAccountActivationToken();
		$customer->data['customer_reset_password_hash'] = $this->z->custauth->hashPassword($activation_token);
		$expires = time() + $this->z->custauth->getConfigValue('reset_password_expires');
		$customer->data['customer_reset_password_expires'] = z::mysqlTimestamp($expires);
		$customer->save();

		$subject = $this->getEmailSubject($this->z->core->t('Registration'));
		$activation_link = sprintf('%s?email=%s&activation_token=%s', $this->z->core->url('activate'), $customer->val('customer_email'), $activation_token);
		$this->z->emails->renderAndSend($email, $subject, 'registration', ['customer' => $customer, 'activation_link' => $activation_link]);
		$this->z->messages->success($this->z->core->t('Thank you for your registration on our website.'));
		$this->z->messages->warning($this->z->core->t('An e-mail was sent to your address with account activation instructions.'));

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

	static function hashPassword($pass) {
		return z::createHash($pass);
	}

	static function verifyPassword($pass, $hash) {
		return z::verifyHash($pass, $hash);
	}

}
