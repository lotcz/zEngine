<?php

	$this->setPageTitle('Banned IP Addresses');

	$this->z->admin->checkIsSuperUser();

	$this->renderAdminTable(
		'banned_ip',
		[
			[
				'name' => 'banned_ip_ip',
				'label' => 'IP',
			],
			[
				'name' => 'banned_ip_date',
				'label' => 'Date'
			]
		],
		null,
		['banned_ip_ip', 'banned_ip_date']
	);
