<?php

require_once __DIR__ . '/../app/models/customer.m.php';
require_once __DIR__ . '/../app/models/custsess.m.php';

class custauthModule extends zModule {

	private $db = null;

	public $customer = null;
	public $session = null;

	public function onEnabled() {
		$this->requireModule('i18n');
		$this->requireModule('messages');
		$this->requireModule('emails');
		$this->requireConfig();
	}

	public function onInit() {
		$this->db = $this->z->core->db;
		$this->checkAuthentication();
		// create anonymous session
		if (!$this->isAuth()) {
			$token = $this->generateToken();
			$this->customer = new CustomerModel($this->db);
			$this->customer->data['customer_anonymous'] = 1;
			$this->customer->data['customer_name'] = $this->z->core->t('Anonymous');
			$this->customer->data['customer_email'] = 'anonymous@' . $token;
			$this->customer->data['customer_language_id'] = 'anonymous@' . $token;
			$this->customer->data['customer_currency_id'] = $this->z->i18n->selected_currency->val('currency_id');
			$this->customer->data['customer_language_id'] = 1;
			$this->customer->save();
			$this->createSession();
		}
	}

	private function createSession($token = null) {
		$ip = $_SERVER['REMOTE_ADDR'];
		// TODO: check if IP address has too many sessions already

		if (!(isset($token))) {
			$token = $this->generateToken();
		}

		$token_hash = Self::hashPassword($token);
		$expires = time() + $this->config['session_expire'];
		$session = new CustomerSessionModel($this->db);
		$session->data['customer_session_token_hash'] = $token_hash;
		$session->data['customer_session_customer_id'] = $this->customer->val('customer_id');
		$session->data['customer_session_expires'] = zSqlQuery::mysqlTimestamp($expires);
		$session->data['customer_session_ip'] = $ip;		
		$session->save();
		setcookie($this->config['cookie_name'], $session->val('customer_session_id') . "-" . $token, $expires, '/', false, false);
		$this->session = $session;
	}

	public function isAuth() {
		return isset($this->customer) && isset($this->session);
	}

	public function isAnonymous() {
		return $this->isAuth() && $this->val('customer_anonymous');
	}

	public function login($email, $password) {

		if (!$this->isAnonymous()) {
			$this->logout();
		}

		$customer = new CustomerModel($this->db);
		$customer->loadByEmail($email);

		if (isset($customer) && $customer->is_loaded) {
			if ($customer->val('customer_failed_attempts') > $this->config['max_attempts']) {
				$this->z->messages->error($this->z->core->t('Max. number of login attempts exceeded. Please ask for new password.'));
			}
			if (Self::verifyPassword($password, $customer->val('customer_password_hash'))) {
				// success - create new session
				$this->customer = $customer;
				$this->createSession();
				$this->updateLastAccess();
				return true;
			} else {
				//TO DO: lof IP failed attempt
				$customer->data['customer_failed_attempts'] += 1;
				$customer->save();
			}
		}

		return false;

	}

	public function loginWithFacebook($access) {

		if (isset($_COOKIE[$this->cookie_name])) {
			$this->logout();
		}

		$customer = new CustomerModel($this->db);
		$customer->loadSingleFiltered(
			'customer_fb_access = ?',
			[$access]
		);

		if ($customer->is_loaded) {
			if ($customer->val('customer_failed_attempts') > $this->config['max_attempts']) {
				die('Max. number of login attempts exceeded. Please ask for new password.');
			}

			$this->customer = $customer;
			$this->createSession();
			$this->updateLastAccess();

		} else {
			//TO DO: lof IP failed attempt
			$customer->data['customer_failed_attempts'] += 1;
			$customer->save();
		}

	}

	public function checkAuthentication() {
		$this->customer = null;

		if (isset($_COOKIE[$this->config['cookie_name']])) {
			$arr = explode('-', $_COOKIE[$this->config['cookie_name']]);
			$session_id = intval($arr[0]);
			$session_token = $arr[1];
		}

		if (isset($session_id)) {
			$this->session = new CustomerSessionModel($this->db, $session_id);
			if (isset($this->session) && $this->session->is_loaded && Self::verifyPassword($session_token, $this->session->val('customer_session_token_hash'))) {
				$expires = time()+$this->config['session_expire'];
				$session = new CustomerSessionModel($this->db);
				$session->data['customer_session_id'] = $session_id;
				$session->data['customer_session_expires'] = zSqlQuery::mysqlTimestamp($expires);
				$session->save();
				setcookie($this->config['cookie_name'], $this->session->val('customer_session_id') . '-' . $session_token, $expires, '/', false, false);
				$this->customer = new CustomerModel($this->db, $this->session->val('customer_session_customer_id'));
				$this->updateLastAccess();
			}
		}

	}

	public function val($name, $def = null) {
		if ($this->isAuth()) {
			return $this->customer->val($name, $def);
		} else {
			return null;
		}
	}

	public function updateLastAccess() {
		if ($this->isAuth()) {
			$customer = new CustomerModel($this->db);
			$customer->data['customer_id'] = $this->customer->val('customer_id');
			$customer->data['customer_last_access'] = zSqlQuery::mysqlTimestamp(time());
			$customer->save();
		}
	}

	public function logout() {
		$this->customer = null;

		if (isset($_COOKIE[$this->config['cookie_name']])) {
			unset($_COOKIE[$this->config['cookie_name']]);
			setcookie($this->config['cookie_name'], '', time()-3600, '/', false, false);
		}

		if (isset($this->session)) {
			$this->session->deleteById();
			$this->session = null;
		}
	}

	public function isValidEmail($email) {
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	public function isValidPassword($password) {
		return (strlen($password) >= $this->config['min_password_length']);
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

	/* EMAILS */

	public function getEmailSubject($text) {
		return sprintf('%s: %s', $this->z->core->getConfigValue('site_title'), $text);
	}

	public function sendRegistrationEmail() {
		$customer_email = $this->customer->val('customer_email');
		$subject = $this->getEmailSubject($this->z->core->t('Thank you for your registration'));
		$this->z->emails->renderAndSend($customer_email, $subject, 'registration', $this->customer);
	}
}
