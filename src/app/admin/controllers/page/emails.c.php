<?php

	$this->setPageTitle('Emails');
	$this->renderAdminTable(
		'email',
		[
			[
				'name' => 'email_to',
				'label' => 'To'
			],
			[
				'name' => 'email_subject',
				'label' => 'Subject'
			],
			[
				'name' => 'email_send_date',
				'label' => 'Send Date'
			],
			[
				'name' => 'email_sent',
				'label' => 'Sent'
			]
		],
		'email',
		['email_to', 'email_subject', 'email_send_date', 'email_sent'],
		'email_send_date desc'
	);
