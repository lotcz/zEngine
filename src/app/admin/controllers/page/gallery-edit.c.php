<?php

	$gallery_id = z::get('gallery_id');

	if (z::isPost()) {
		// image upload
		$this->z->gallery->uploadImage($gallery_id, 'image_file');
		//$this->setData('json', ['result' => 'UPLOADED']);
	}

	if (z::isMethod('DELETE')) {
		// image delete
		$image_id = z::get('image_id');
		$this->z->gallery->deleteImage($image_id);
		//$this->setData('json', ['result' => 'DELETED']);
	}

	$images = $this->z->gallery->loadGalleryImages($gallery_id);

	$this->setMasterView('plain');
	$this->setData('images', $images);
	$this->setData('gallery_id', $gallery_id);
