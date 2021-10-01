<?php
	$this->setPageTitle('Change Password');

	if (z::isPost()) {

		$password = z::get('password');
		$password_confirm = z::get('password_confirm');
		$user_id = z::getInt('user_id');

		if ($this->z->auth->isValidPassword($password)) {

			if ($password == $password_confirm) {
				if ($user_id > 0) {
					if ($this->z->isModuleEnabled('admin') && $this->z->admin->isSuperUser()) {
						$user = new UserModel($this->z->db, $user_id);
						if ($user->is_loaded) {
							$user->set('user_password_hash', $this->z->auth->hashPassword($password));
							$user->save();
							$this->message("User password was successfully changed for user '$user_id'.", 'success');
						} else $this->z->messages->error($this->t("User ID '$user_id' not found!"));
					} else $this->z->messages->error($this->t('You must be superuser to change other people\'s passwords!'));
				} else {
					$user = $this->z->auth->user;
					$user->set('user_password_hash', $this->z->auth->hashPassword($password));
					$user->save();
					$this->message('Your password was successfully changed.', 'success');
				}
			} else {
				$this->z->messages->error($this->t('Passwords don\'t match.'));
			}
		} else {
			$this->z->messages->error($this->t('Invalid password.'));
		}

	}
