<?php
	$this->requireModule('forms');
	$this->setPageTitle('Change password');

	if (!($this->isCustAuth() && !$this->z->custauth->isAnonymous())) {
		$this->redirect('login');
	}
	
	$form = new zForm('changepass_form');
	$form->add([
		[
			'name' => 'password',
			'label' => 'Password',
			'type' => 'password',
			'validations' => [['type' => 'password']]
		],
		[
			'name' => 'password_confirm',
			'label' => 'Confirm Password',
			'type' => 'password',
			'validations' => [['type' => 'confirm', 'param' => 'password']]
		]
	]);

	if (z::isPost()) {
		
		$password = z::get('password');
		$password_confirm = z::get('password_confirm');

		if ($this->z->custauth->isValidPassword($password)) {

			if ($password == $password_confirm) {	
				
				$customer = $this->getCustomer();				
				$customer->data['customer_password_hash'] = $this->z->custauth->hashPassword($password);
				$customer->save();				
				$this->message('Your password was successfully changed.', 'success');
				
			} else {
				$this->z->messages->error($this->t('Passwords don\'t match.'));
			}
		} else {
			$this->z->messages->error($this->t('Invalid password.'));
		}

	}