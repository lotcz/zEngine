<?php

require_once __DIR__ . '/../models/customer.m.php';

/**
* Module that handles shopping
*/
class shopModule extends zModule {

	public $depends_on = ['db', 'i18n', 'emails'];

	public $customer = null;
	public $session = null;

	public function onEnabled() {
		$this->requireConfig();
	}

	public function OnBeforeInit() {
		$this->checkAuthentication();
	}

	public function createAnonymousSession() {
		$this->z->auth->createAnonymousSession();
		$customer = new CustomerModel($this->z->db);
		$customer->data['customer_currency_id'] = $this->z->i18n->selected_currency->val('currency_id');
		$user->save();
		$this->createSession($user);
	}

	public function createSession($customer, $token = null) {
		$ip = $_SERVER['REMOTE_ADDR'];
		// TODO: check if IP address has too many sessions already

		$this->customer = $customer;

		if (!(isset($token))) {
			$token = $this->generateSessionToken();
		}

		$token_hash = Self::hashPassword($token);
		$expires = time() + $this->config['session_expire'];
		$session = new CustomerSessionModel($this->z->db);
		$session->data['customer_session_token_hash'] = $token_hash;
		$session->data['customer_session_customer_id'] = $this->customer->val('customer_id');
		$session->data['customer_session_expires'] = z::mysqlTimestamp($expires);
		$session->data['customer_session_ip'] = $ip;
		$session->save();
		setcookie($this->config['cookie_name'], $session->val('customer_session_id') . "-" . $token, $expires, '/', false, false);
		$this->session = $session;

		$this->updateLastAccess();
	}

	/**
	* Return true if customer is authenticated.
	*/
	public function isAuth() {
		return isset($this->customer) && isset($this->session);
	}

	/**
	* Return true if the is no authenticated customer or authenticated customer is anonymous.
	*/
	public function isAnonymous() {
		return ((!$this->isAuth()) || $this->customer->isAnonymous());
	}

	/**
	* Perform login for given username/email and password by creating a session. Return true if successful.
	*/
	public function login($email, $password) {

		$old_customer_id = null;
		if ($this->isAuth()) {
			$old_customer_id = $this->customer->ival('customer_id');
		}

		$this->logout();

		$customer = new CustomerModel($this->z->db);
		$customer->loadByEmail($email);

		if (isset($customer) && $customer->is_loaded) {
			if ($customer->val('customer_failed_attempts') < $this->config['max_attempts']) {
				if ($customer->isActive()) {
					if (Self::verifyPassword($password, $customer->val('customer_password_hash'))) {

						// success - create new session
						$this->createSession($customer);

						//if user put any products into cart before logging in, copy cart products
						if ($this->z->isModuleEnabled('cart') && isset($old_customer_id)) {
							$this->z->cart->transferCart($old_customer_id, $this->customer->ival('customer_id'));
						}
						return true;
					} else {
						$customer->data['customer_failed_attempts'] += 1;
						$customer->save();
						IpFailedAttemptModel::saveFailedAttempt($this->z->db);
					}
				} else {
					$this->z->messages->warning($this->z->core->t('--account-not-active--'));
				}
			} else {
				$this->z->messages->error($this->z->core->t('Max. number of login attempts exceeded. Please ask for new password.'));
			}
		}
		return false;
	}

	/**
	* Verifies if there is customer logged in.
	* Call this only once in the beginning of request processing and then call to isAuth() method
	* to check whether customer is authenticated.
	*/
	public function checkAuthentication() {
		$this->customer = null;

		// TO DO: authenticate customer

	}

	public function registerCustomer($email, $password, $full_name) {
		$customer = new CustomerModel($this->z->db);
		$customer->data['customer_name'] = $full_name;
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

	/* EMAILS */

	public function getEmailSubject($text) {
		return sprintf('%s: %s', $this->z->core->getConfigValue('site_title'), $text);
	}

}
