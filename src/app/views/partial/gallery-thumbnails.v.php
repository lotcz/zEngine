<?php
	$thumbnail_format = 'thumb';
	$full_format = 'view';
?>
<div class="gallery-thumbnails">
	<?php
		foreach ($images as $image) {
			?>
				<div
					class="gallery-thumbnail"
					data-gallery-image-url="<?=$this->z->images->getImageUrl($image->val('image_path'), $full_format) ?>"
				>
					<?php
						$this->z->images->renderImage($image->val('image_path'), $thumbnail_format);
						$this->z->images->prepareImage($image->val('image_path'), 'view');
					?>
				</div>
			<?php
		}
	?>
</div>
