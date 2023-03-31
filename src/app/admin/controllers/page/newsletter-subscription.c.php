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
				'label' => 'Zasílat newslettery',
				'type' => 'bool',
				'hint' => 'Je-li tento příznak vypnutý, znamená to, že adresa se odhlásila a nepřeje si dostávat newsletter. Nikdy nemažte neaktivní adresy - mohlo by dojít k jejich opětovnému importu a nevyžádanému zasílání!'
			],
		]
	);
