<?php
	
	require_once __DIR__ . '/../../../models/ip_failed.m.php';
	$this->renderAdminForm(
		'ip_failed_attempt',
		'IpFailedAttemptModel',
		[		
			[
				'name' => 'ip_failed_attempt_ip',
				'label' => 'IP',
				'type' => 'text',
				'validations' => [['type' => 'ip']]
			],
			[
				'name' => 'ip_failed_attempt_count',
				'label' => 'Failed Attempts',
				'type' => 'text',
				'validations' => [['type' => 'integer']]
			],
			[
				'name' => 'ip_failed_attempt_first',
				'label' => 'First',
				'type' => 'date',
				'hint' => 'Date of the first failed attempt.',
				'validations' => [['type' => 'date']]
			],
			[
				'name' => 'ip_failed_attempt_last',
				'label' => 'Last',
				'type' => 'date',
				'hint' => 'Date of the last failed attempt.',
				'validations' => [['type' => 'date']]
			]		
		]
	);