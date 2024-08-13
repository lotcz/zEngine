<?php

	require_once __DIR__ . '/../../../models/email.m.php';

	$this->z->admin->checkAnyRole();

	$this->renderAdminForm(
		'EmailModel',
		[
			[
				'name' => 'email_from',
				'label' => 'From',
				'type' => 'text'
			],
			[
				'name' => 'email_to',
				'label' => 'To',
				'type' => 'text'
			],
			[
				'name' => 'email_subject',
				'label' => 'Subject',
				'type' => 'text'
			],
			[
				'name' => 'email_content_type',
				'label' => 'Content Type',
				'type' => 'text'
			],
			[
				'name' => 'email_body',
				'label' => 'Body',
				'type' => 'wysiwyg'
			],
			[
				'name' => 'email_send_date',
				'label' => 'Send Date',
				'type' => 'date'
			],
			[
				'name' => 'email_sent',
				'label' => 'Sent',
				'type' => 'bool'
			]
		]
	);
