<?php

	$this->setPageTitle('Failed Attempts');
	$this->renderAdminTable(
		'ip_failed_attempt',
		[
			[
				'name' => 'ip_failed_attempt_ip',
				'label' => 'IP',
			],
			[
				'name' => 'ip_failed_attempt_count',
				'label' => 'Counter'
			],
			[
				'name' => 'ip_failed_attempt_first',
				'label' => 'First failed attempt'
			],
			[
				'name' => 'ip_failed_attempt_last',
				'label' => 'Last failed attempt'
			]
		],
		null,
		['ip_failed_attempt_ip', 'ip_failed_attempt_count', 'ip_failed_attempt_first', 'ip_failed_attempt_last']
	);
