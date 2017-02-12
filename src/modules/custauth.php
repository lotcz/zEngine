<?php
require_once $home_dir . 'models/currency.m.php';
require_once $home_dir . 'models/customer.m.php';
require_once $home_dir . 'models/custsess.m.php';

/*
	provides customer authentication mechanism
*/
class CustomerAuthentication {
	
	private $db = null;
	
	private $cookie_name = 'customer_session_token';
		
	public $customer = null;
	public $session = null;
	public $customer_id = 0;
	
	static $max_attempts = 100;
	static $min_password_length = 5;
	static $session_expire = 60*60*24*7; //7 days
	
	function __construct($db) {
		$this->db = $db;	
		$this->checkAuthentication();		
		
		// create anonymous session
		if (!$this->isAuth()) {
			$token = $this->generateToken();
			$this->customer = new Customer($db);
			$this->customer->data['customer_anonymous'] = 1;
			$this->customer->data['customer_name'] = t('Anonymous');
			$this->customer->data['customer_email'] = 'anonymous@' . $token;
			$this->customer->data['customer_language_id'] = 'anonymous@' . $token;
			$currency = Currency::getSelectedCurrency($db);
			$this->customer->data['customer_currency_id'] = $currency->val('currency_id');
			$this->customer->data['customer_language_id'] = 1;
			$this->customer->save();
			$this->createSession();
		}	
	}

	private function createSession($token = null) {
		// TODO: check if IP address has too many sessions already
		
		if (!(isset($token))) {
			$token = $this->generateToken();
		}
		
		$token_hash = CustomerAuthentication::hashPassword($token);
		$expires = time() + CustomerAuthentication::$session_expire;
		$session = new CustomerSession($this->db);
		$session->data['customer_session_token_hash'] = $token_hash;
		$session->data['customer_session_customer_id'] = $this->customer->val('customer_id');
		$session->data['customer_session_expires'] = SqlQuery::mysqlTimestamp($expires);
		$session->save();
		setcookie($this->cookie_name, $session->val('customer_session_id') . "-" . $token, $expires, '/', false, false); 				
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
		
		$customer = new Customer($this->db);
		$customer->loadByEmail($email);
		
		if (isset($customer) && $customer->is_loaded) {
			if ($customer->val('customer_failed_attempts') > $this::$max_attempts) {
				$messages[] = t('Max. number of login attempts exceeded. Please ask for new password.');
			}
			if (CustomerAuthentication::verifyPassword($password, $customer->val('customer_password_hash'))) {
				// success - create new session				
				$this->customer = $customer;
				$this->createSession();
				$this->updateLastAccess();
				return true;
			} else {
				// TODO: log IP failed attempt
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
		
		$customer = new Customer($this->db);
		$customer->loadSingleFiltered(
			'customer_fb_access = ?',
			[$access]
		);
		
		if ($customer->is_loaded) {
			if ($customer->val('customer_failed_attempts') > $this::$max_attempts) {
				die('Max. number of login attempts exceeded. Please ask for new password.');
			}
						
			$this->customer = $customer;
			$this->createSession();
			$this->updateLastAccess();
			
		} else {
			// log ip failed attempts
			//$customer->data['customer_failed_attempts'] += 1;
			//$customer->save();
		}
		
	}
	
	public function checkAuthentication() {
		$this->customer = null;
						
		if (isset($_COOKIE[$this->cookie_name])) {
			$arr = explode('-', $_COOKIE[$this->cookie_name]);
			$session_id = intval($arr[0]);
			$session_token = $arr[1];
		}
		
		if (isset($session_id)) {
			$this->session = new CustomerSession($this->db, $session_id);			
			if (isset($this->session) && $this->session->is_loaded && CustomerAuthentication::verifyPassword($session_token, $this->session->val('customer_session_token_hash'))) {
				$expires = time()+CustomerAuthentication::$session_expire;
				$session = new CustomerSession($this->db);
				$session->data['customer_session_id'] = $session_id;
				$session->data['customer_session_expires'] = SqlQuery::mysqlTimestamp($expires);
				$session->save();
				setcookie($this->cookie_name, $this->session->val('customer_session_id') . '-' . $session_token, $expires, '/', false, false); 				
				$this->customer = new Customer($this->db, $this->session->val('customer_session_customer_id'));				
				$this->updateLastAccess();
			}
		}
		
		if ($this->isAuth()) {
			$this->customer_id = $this->customer->val('customer_id');
		} else {
			$this->customer_id = 0;
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
			$customer = new Customer($this->db);
			$customer->data['customer_id'] = $this->customer->val('customer_id');
			$customer->data['customer_last_access'] = SqlQuery::mysqlTimestamp(time());
			$customer->save();
		}
	}
	
	public function logout() {
		$this->customer = null;
		
		if (isset($_COOKIE[$this->cookie_name])) {			
			unset($_COOKIE[$this->cookie_name]);
			setcookie($this->cookie_name, '', time()-3600, '/', false, false);
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
		return (strlen($password) >= CustomerAuthentication::$min_password_length);
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