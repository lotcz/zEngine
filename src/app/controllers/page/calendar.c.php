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

			if (!zForm::validate_email($email)) {
				$code = 400;
				$json->message = $this->t('Invalid email address!');
			} else {
				$is_valid_user = $this->z->auth->isAuth() && $this->z->auth->user->isActive();
				// AUTH
				if ($is_valid_user) {
					// admin
					if ($this->z->admin->isAdmin()) {
						$user = $this->z->auth->loadUserByLoginOrEmail($email);
						if (!$user) {
							$user = $this->z->auth->createUser(null, null, $email, null, $email, UserModel::user_state_active, UserRoleModel::role_external);
						}
						$reservation = $this->z->calendar->saveReservationJson($user->ival('user_id'), $res);
						$json->result = $reservation->getJson();
						$json->message = $this->t('Rezervace byla uložena.');
					// not admin
					} else {
						if ($this->z->auth->user->get('user_email') === $email) {
							$reservation = $this->z->calendar->saveReservationJson($this->z->auth->user->ival('user_id'), $res);
							$json->result = $reservation->getJson();
							$json->message = $this->t('Rezervace byla uložena.');
						} else {
							$code = 401;
							$json->message = $this->t('Access Forbidden');
						}
					}
				// NOT AUTH
				} else {
					$code = 401;
					$json->message = $this->t('Access Forbidden');
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
