<?php

	$this->z->staticpages->activateEditor();

	$onAfterUpdate = function($z, $form, $static_page) {
		// update alias
		$a = new AliasModel($z->core->db, $static_page->ival('static_page_alias_id'));
		$page_need_update = !$a->is_loaded;

		$a->setUrl($static_page->getAliasUrl());
		$a->set('alias_path', $static_page->getViewPath());
		$a->save();

		if ($page_need_update) {
			$static_page->set('static_page_alias_id', $a->ival('alias_id'));
			$static_page->save();
		}
	};

	$onBeforeDelete = function($z, $form, $static_page_id) {
		// delete alias
		$static_page = new StaticPageModel($z->core->db, $static_page_id);
		$alias_id = $static_page->ival('static_page_alias_id');
		if (isset($alias_id)) {
			AliasModel::del($z->core->db, $alias_id);
		}
	};

	$this->renderAdminForm(
		'static_page',
		'StaticPageModel',
		[
			[
				'name' => 'static_page_title',
				'label' => 'Title',
				'type' => 'text',
				'validations' => [['type' => 'length', 'param' => 1], ['type' => 'maxlen', 'param' => 100]]
			],
			[
				'name' => 'static_page_content',
				'label' => 'Content',
				'type' => 'html',
				'validations' => [['type' => 'html']]
			]
		],
		null,
		$onAfterUpdate,
		$onBeforeDelete,
		null
	);
