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
				'name' => 'newsletter_subscription_state',
				'label' => 'Stav',
				'hint' => 'Pouze adresy označené jako aktivní budou dostávat newsletter. Nikdy nemažte adresy - mohlo by dojít k jejich opětovnému importu a nevyžádanému zasílání! Raději je označte jako odhlášené nebo neplatné',
				'type' => 'select',
				'select_id_field' => 'id',
				'select_label_field' => 'label',
				'select_data' => $this->z->newsletter->getSubscriptionStates()
			]
		],
		function ($z, $form, $data) {
			$data->set('newsletter_subscription_state_changed', z::mysqlTimestamp());
		}
	);
