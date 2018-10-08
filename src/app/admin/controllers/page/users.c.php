<?php

	$this->setPageTitle('Users');
	$this->renderAdminTable(
		'user',
		'user',
		[
			[
				'name' => 'user_name',
				'label' => 'Full name'
			],
			[
				'name' => 'user_login',
				'label' => 'Login'
			],
			[
				'name' => 'user_email',
				'label' => 'E-mail'
			],
			[
				'name' => 'user_last_login',
				'label' => 'Last Login'
			]
		]
	);
