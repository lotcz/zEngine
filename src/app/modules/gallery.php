<?php

require_once __DIR__ . '/../models/gallery.m.php';
require_once __DIR__ . '/../models/image.m.php';

/**
* Module that handles image galleries.
*/
class galleryModule extends zModule {

	public $depends_on = ['db', 'images'];

	function onEnabled() {
		$this->requireConfig();

		$this->z->core->includeJS('resources/gallery/gallery-admin.js', 'admin.bottom');
		$this->z->core->includeCSS('resources/gallery/gallery-admin.css', 'admin.head');
		$this->z->core->includeJS('resources/gallery/gallery.js', 'bottom');
		$this->z->core->includeCSS('resources/gallery/gallery.css', 'head');
	}

	function createGallery(string $name = '') {
		$gallery = new GalleryModel($this->z->db);
		$gallery->set('gallery_name', $name);
		$gallery->save();
		return $gallery;
	}

	private function saveImageInternal(int $gallery_id, string $image_path) {
		$image = new ImageModel($this->z->db);
		$image->set('image_gallery_id', $gallery_id);
		$image->set('image_path', $image_path);
		$image->save();
		return $image;
	}

	function uploadImage(int $gallery_id, string $image_field_name) {
		$upload_result = $this->z->images->uploadImage($image_field_name);
		if (empty($upload_result)) {
			return null;
		}

		if (is_array($upload_result)) {
			$results = [];
			foreach ($upload_result as $image_path) {
				$results[] = $this->saveImageInternal($gallery_id, $image_path);
			}
			return $results;
		}

		return $this->saveImageInternal($gallery_id, $upload_result);
	}

	private function deleteImageInternal(ImageModel $image) {
		$this->z->images->deleteImage($image->val('image_path'));
		$image->delete();
	}

	function deleteImage(int $image_id = null) {
		$image = new ImageModel($this->z->db, $image_id);
		$this->deleteImageInternal($image);
	}

	function deleteGallery(int $gallery_id = null) {
		$images = $this->loadGalleryImages($gallery_id);
		foreach ($images as $image) {
			$this->deleteImageInternal($image);
		}
		GalleryModel::deleteById($this->z->db, $gallery_id);
	}

	function loadGalleryImages(int $gallery_id) {
		return ImageModel::select($this->z->db, 'image', 'image_gallery_id = ?', 'image_id', null, [$gallery_id], [PDO::PARAM_INT]);
	}

	function renderGalleryForm(int $gallery_id) {
		$this->z->core->renderPartialView('gallery-form', ['gallery_id' => $gallery_id]);
	}

	function renderGallery(int $gallery_id) {
		$images = $this->loadGalleryImages($gallery_id);
		$this->z->core->renderPartialView('gallery-thumbnails', ['images' => $images]);
	}

}
