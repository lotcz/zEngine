<div class="gallery-admin-wrapper">
	<div class="gallery-form">
		<form id="image_form" method="POST" enctype="multipart/form-data">
			<input type="hidden" name="gallery_id" value="<?=$gallery_id?>" />
			<div class="gallery-upload">
				<input id="gallery_image_file" name="image_file" type="file" class="form-control-file" />
				<label for="gallery_image_file"><?=$this->t('Upload Image'); ?></label>
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

<script>

	window.onload = () => {

		const elements = [...document.querySelectorAll('[data-bs-toggle="popover"]')];
		const popovers = elements.map(
			(el) => {
				const content = el.querySelector('.popover-content > *');
				const popover = new bootstrap.Popover(
					el,
					{
						html: true,
						content: content,
						customClass: 'popover-value-to-clipboard'
					}
				);
				el.addEventListener(
					'click',
					() => {
						popovers.forEach((p) => {
							if (p !== popover) p.hide();
						});
						popover.toggle();
					}
				);
				return popover;
			}
		);
	};

</script>
