<?php
	
	$this->setPageTitle('Payment types');	
	$this->renderAdminTable(
		'payment_types', 		
		'payment_type',
		[		
			[
				'name' => 'payment_type_name',
				'label' => 'Name'			
			]
		]
	);