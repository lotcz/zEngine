<?php

	require_once __DIR__ . '/../../../models/newsletter_subscription.m.php';

	$this->renderAdminForm(
		'NewsletterSubscriptionModel',
		[
			[
				'name' => 'newsletter_subscription_email',
				'label' => 'E-mail',
				'type' => 'text'
			],
			[
				'name' => 'newsletter_subscription_active',
				'label' => 'Active',
				'type' => 'bool'
			],
		]
	);
