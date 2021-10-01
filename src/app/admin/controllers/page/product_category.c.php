<?php

	$form = new zForm('category');
	$form->render_wrapper = true;
	$this->setPageTemplate('admin');
	
	$form->add([
		[
			'name' => 'category_id',
			'type' => 'hidden'
		],
		[
			'name' => 'category_name',
			'label' => 'Name',
			'type' => 'text'
		],
		[
			'name' => 'category_description',
			'label' => 'Description',
			'type' => 'text'
		],
		[
			'name' => 'category_parent_id',
			'label' => 'Parent Category',
			'type' => 'select',
			'select_table' => 'categories',
			'select_id_field' => 'category_id',
			'select_label_field' => 'category_name',
			'empty_option_name' => '...'
		]		
	]);
	
	
	if (isset($_POST['category_id'])) {
		$category = new CategoryModel($this->db, $_POST['category_id']);
		$category->setData($form->processInput($_POST));	
		$category->data['category_parent_id'] = parseInt($category->val('category_parent_id'));
		$alias_url = $category->val('alias_url');
		unset($category->data['alias_url']);
		$category->save();
		
		$alias = new AliasModel($this->db, $category->ival('category_alias_id'));
		
		// save alias if new or changed
		if ($alias->val('alias_url') != $alias_url || !$alias->is_loaded) {
			$alias->data['alias_path'] = $category->getViewPath();
			if (isset($alias_url) && strlen(trim($alias_url)) > 0) {
				$alias->setUrl($alias_url);
			} else {
				$alias->setUrl($category->getAliasUrl());
			}			
			$alias->save();
		}
		
		// update category alias if changed
		if ($alias->ival('alias_id') != $category->ival('category_alias_id')) {
			$category->data['category_alias_id'] = $alias->ival('alias_id');
			$category->save();
		}
		
		$category->alias = $alias;

	} elseif ($this->getPath(3) == 'edit') {
		$category = new CategoryModel($this->db, $this->getPath(4));
		$category->alias = new AliasModel($this->db, $category->val('category_alias_id'));
		$this->setPageTitle('Editing Category');
	} elseif ($this->getPath(3) == 'delete') {
		$category = new CategoryModel($this->db);
		$category->deleteById($this->getPath(4));
		$ret = get('r', 'admin/categories');
		redirect($ret);
	} else {
		$category = new CategoryModel($this->db);
		$this->setPageTitle('New Category');
	}
	
	$form->prepare($this->db, $category);	
	$form->addField(
		[
			'name' => 'form_buttons',				
			'type' => 'buttons',
			'buttons' => $this->z->admin->getAdminFormButtons($form)
		]
	);
		
	$this->setData('form', $form);