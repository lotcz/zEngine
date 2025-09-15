<?php

	$this->setPageTitle('Account deactivation');

	if (!($this->z->auth->isAuth() && !$this->z->auth->isAnonymous())) {
		$this->redirect('login');
	}

	if (z::isPost()) {
		$email = z::get('email');
		if ($email === $this->z->auth->getUser()->val('user_email')) {
			$this->z->auth->deactivateAccount();
			$this->z->messages->error($this->t('Your account was successfully deactivated. Good bye.'));
			$this->setPageView('error');
		} else {
			$this->z->messages->error($this->t('Emails don\'t match.'));
		}
	}
