<div class="gallery-image">
	<div class="gallery-image-file">
		<?php
			$this->z->images->renderImage($image->val('image_path'), 'mini');
		?>
		<form method="POST">
			<input type="hidden" name="delete_image_id" value="<?=$image->val('image_id')?>" />
			<input type="hidden" name="gallery_id" value="<?=$image->val('image_gallery_id')?>" />
			<input type="submit" value="<?=$this->t('Delete')?>" />
		</form>
	</div>
</div>
