<?php

	$this->z->admin->checkAnyRole();
	$this->z->requireModule('dbcache');

	$onBeforeUpdate = function($z, $form, $data) {
		$data->set('dbcache_key_hash', $z->dbcache->getKeyHash($data->val('dbcache_key')));
	};

	$this->renderAdminForm(
		'DbCacheModel',
		[
			[
				'name' => 'dbcache_key_hash',
				'label' => 'Hash',
				'type' => 'static'
			],
			[
				'name' => 'dbcache_key',
				'label' => 'Key',
				'type' => 'text',
				'validations' => [
					['type' => 'length', 'param' => 1]
				]
			],
			[
				'name' => 'dbcache_value',
				'label' => 'Value',
				'type' => 'text',
				'validations' => [
					['type' => 'length', 'param' => 1]
				]
			]
		],
		$onBeforeUpdate, //before update
		null, //after update
		null, //before delete
		null //after delete
	);
