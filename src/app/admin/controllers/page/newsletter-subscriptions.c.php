<?php

	$this->setPageTitle('Subscriptions');
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
				'name' => 'newsletter_subscription_state',
				'label' => 'Stav'
			],
			[
				'name' => 'newsletter_subscription_state_changed',
				'label' => 'Stav změnen'
			]
		],
		'newsletter_subscription',
		['newsletter_subscription_id', 'newsletter_subscription_email', 'newsletter_subscription_state', 'newsletter_subscription_state_changed'],
		'newsletter_subscription_id',
		['newsletter_subscription_email']
	);
