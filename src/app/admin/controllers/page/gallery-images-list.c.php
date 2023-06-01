<?php

	$gallery_id = z::getInt('gallery_id');
	$images = $this->z->gallery->loadGalleryImages($gallery_id);

	$json = [];

	foreach ($images as $image) {
		$json[] = [
			'title' => $image->val('image_path'),
			'value' => $this->z->images->img($image->val('image_path'), 'view')
		];
	}

	$this->setMasterView('json');
	$this->require_page_view = false;
	$this->setData('json', $json);

