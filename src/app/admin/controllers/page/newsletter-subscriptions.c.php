<?php

	$this->setPageTitle('Newsletter Subscriptions');
	$this->renderAdminTable(
		'newsletter_subscription',
		[
			[
				'name' => 'newsletter_subscription_id',
				'label' => 'ID'
			],
			[
				'name' => 'newsletter_subscription_email',
				'label' => 'E-mail'
			],
			[
				'name' => 'newsletter_subscription_active',
				'label' => 'Active'
			]
		],
		'newsletter_subscription',
		['newsletter_subscription_id', 'newsletter_subscription_email', 'newsletter_subscription_active'],
		'newsletter_subscription_id',
		['newsletter_subscription_email']
	);
