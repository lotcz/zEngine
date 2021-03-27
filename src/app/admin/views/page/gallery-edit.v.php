<div>
	<div class="gallery-form">
		<form id="image_form" action="<?=$this->url('admin/plain/default/gallery-edit')?>" method="POST" enctype="multipart/form-data">
			<input type="hidden" name="gallery_id" value="<?=$gallery_id?>" />
			<div class="gallery-upload">
				<input id="gallery_image_file" name="image_file" type="file" class="form-control-file" />
				<label for="gallery_image_file">Upload new image</label>
			</div>
		</form>
	</div>
	<div class="gallery-images">
		<?php
			foreach ($images as $image) {
				$this->z->core->renderPartialView('gallery-image', ['image' => $image]);
			}
		?>
	</div>
</div>
