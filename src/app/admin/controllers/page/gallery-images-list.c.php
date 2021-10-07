<?php

	$article_id = z::getInt('article_id');
	$article = new ArticleModel($this->z->db, $article_id);

	$gallery_id = $article->ival('article_gallery_id');

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
	
