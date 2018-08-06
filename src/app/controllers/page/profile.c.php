<?php
	$this->requireModule('forms');
	$this->setPageTitle('User Profile');

	if (!$this->isCustAuth() || $this->z->custauth->isAnonymous()) {
		$this->redirect('login');
	} else {
		$form = new zForm('customer');
		$form->type = '';
		$form->add([
			[
			  'name' => 'customer_email',
			  'label' => 'E-mail',
			  'type' => 'text',
				'disabled' => 'disabled'
			],
			[
			'name' => 'customer_name',
			'label' => 'Full name',
			'type' => 'text'
			]
	 	]);
		$customer = $this->getCustomer();
		if (z::isPost()) {
			$customer->set('customer_name', z::get('customer_name'));
			$customer->save();
		}
		$form->prepare($this->db, $customer);
		$this->setData('form', $form);
	}
