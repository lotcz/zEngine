<?php

	$gallery_id = z::get('gallery_id');

	if (z::isPost()) {
		// delete image
		$image_id = z::getInt('delete_image_id');
		if ($image_id !== null && $image_id > 0) {
			$this->z->gallery->deleteImage($image_id);
		} else {
			// upload image
			$this->z->gallery->uploadImage($gallery_id, 'image_file');
		}
	}

	$images = $this->z->gallery->loadGalleryImages($gallery_id);

	$this->setMasterView('plain');
	$this->setData('images', $images);
	$this->setData('gallery_id', $gallery_id);
