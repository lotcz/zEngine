<?php

	require_once __DIR__ . '/../../../models/banned_ip.m.php';

	$this->renderAdminForm(
		'BannedIpModel',
		[
			[
				'name' => 'banned_ip_ip',
				'label' => 'IP',
				'type' => 'text',
				'validations' => [['type' => 'ip']]
			],
			[
				'name' => 'banned_ip_date',
				'label' => 'Date',
				'type' => 'date',
				'validations' => [['type' => 'date']]
			]
		]
	);
