<?php

	if (z::isPost()) {
		$code = 200;
		$json = (object) [
			'result' => null,
			'message' => 'OK'
		];

		try {
			$res = json_decode(z::getRequestBody());
			$email = property_exists($res, 'email') ? z::trim($res->email) : '';
			$this->requireModule('forms');
			if (!zForm::validate_email($email)) {
				$code = 400;
				$json->message = $this->t('Invalid email address!');
			} else {
				// AUTH
				if ($this->z->auth->isAuth()) {
					// admin
					if ($this->z->admin->isAdmin()) {
						$user = $this->z->auth->loadUserByLoginOrEmail($email);
						if (!$user) {
							$user = $this->z->auth->createUser($email, $email, $email, $email, UserModel::user_state_active, UserRoleModel::role_external);
						}
						$reservation = $this->z->calendar->saveReservationJson($user->ival('user_id'), $res);
						$json->result = $reservation->getJson();
						$json->message = $this->t('Your reservation was saved.');
					// not admin
					} else {
						if ($this->z->auth->isAnonymous()) {
							$code = 200;
							$this->z->auth->registerUser($email, $email, $email, z::generateRandomToken(10));
							$json->message = $this->t('An e-mail was sent to your address with account activation instructions.');
						} else {
							if ($this->z->auth->user->isActive()) {
								if ($this->z->auth->user->get('user_email') === $email) {
									$reservation = $this->z->calendar->saveReservationJson($this->z->auth->user->ival('user_id'), $res);
									$json->result = $reservation->getJson();
								} else {
									if ($this->z->auth->emailExists($email)) {
										$code = 401;
										$json->message = $this->t('Přihlašte se');
									} else {
										$code = 403;
										$json->result = $this->z->auth->user->get('user_email');
										$json->message = $this->t('Již máte registraci pod jiným emailem.');
									}
								}
							} else {
								$code = 400;
								$json->message = $this->t('An e-mail was sent to your address with account activation instructions.');
							}
						}
					}
				// NOT AUTH
				} else {
					if ($this->z->auth->emailExists($email)) {
						$code = 401;
						$json->message = $this->t('Přihlašte se prosím zde');
					} else {
						$code = 200;
						$user = $this->z->auth->registerUser($email, $email, $email, z::generateRandomToken(10));
						$this->z->auth->createSession($user);
						$json->message = $this->t('An e-mail was sent to your address with account activation instructions.');
					}
				}
			}

		} catch (Exception $e) {
			$code = 500;
			$json->message = $e->getMessage();
		}
		http_response_code($code);
		$this->setData('json', $json);
	} else if (z::isMethod('DELETE')) {
		$this->z->calendar->deleteReservationById(z::getInt('id'));
	} else {
		$from = z::parseDatetime(z::get('from'));
		$to = z::parseDatetime(z::get('to'));
		$json = $this->z->calendar->loadReservationsJson($from, $to);
		$this->setData('json', $json);
	}
