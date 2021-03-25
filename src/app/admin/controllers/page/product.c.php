<?php

	require_once __DIR__ . '/../../../models/product.m.php';
	require_once __DIR__ . '/../../../models/product_category.m.php';

	$this->requireModule('tinymce');

	//fill in slug value before update
	$onBeforeUpdate = function($z, $form, $data) {
		$data->set('product_slug', $z->alias->slugify($data->val('product_name')));

		// remember old image
		$product = new ProductModel($z->db, $data->ival('product_id'));
		$form->productOldImage = $product->val('product_image');
	};

	//save slug as alias and delete old image after update
	$onAfterUpdate = function($z, $form, $data) {
		$product_slug = $data->val('product_slug');
		$product_path = ProductModel::getProductPath($data->val('product_id'));
		$z->alias->createUrlIfNotExists($product_slug, $product_path);

		// delete old image
		if ((!empty($form->productOldImage)) && ($form->productOldImage != $data->val('product_image')))
			$z->images->deleteImage($form->productOldImage);
	};

	// remember image name before delete
	$onBeforeDelete = function($z, $form, $id) {
		$product = new ProductModel($z->db, $id);
		$form->productDeleteImage = $product->val('product_image');
	};

	// delete alias(es) and image after delete
	$onAfterDelete = function($z, $form, $id) {
		$product_path = ProductModel::getProductPath($id);
		$z->alias->deleteAllForPath($product_path);

		if (!empty($form->productDeleteImage))
			$z->images->deleteImage($form->productDeleteImage);
	};

	$this->renderAdminForm(
		'ProductModel',
		[
			[
				'name' => 'product_product_category_id',
				'label' => 'Category',
				'type' => 'select',
				'select_table' => 'product_category',
				'select_data' => ProductCategoryModel::all($this->z->db),
				'select_id_field' => 'product_category_id',
				'select_label_field' => 'product_category_name'
			],
			[
				'name' => 'product_name',
				'label' => 'Name',
				'type' => 'text',
				'validations' => [['type' => 'length']]
			],
			[
				'name' => 'product_slug',
				'label' => 'URL',
				'type' => 'text'
			],
			[
				'name' => 'product_price',
				'label' => 'Price',
				'type' => 'text',
				'validations' => [['type' => 'price']]
			],
			[
				'name' => 'product_description',
				'label' => 'Description',
				'type' => 'tinymce'
			],
			[
				'name' => 'product_image',
				'label' => 'Image',
				'type' => 'image',
				'image_size' => 'thumb'
			]
		],
		$onBeforeUpdate,
		$onAfterUpdate,
		$onBeforeDelete,
		$onAfterDelete
	);
