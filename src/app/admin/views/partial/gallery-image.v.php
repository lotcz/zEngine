<div class="gallery-image">
	<div class="card">

		<div class="card-header p-2">
			<div>
				<form method="POST">
					<input type="hidden" name="delete_image_id" value="<?=$image->val('image_id')?>" />
					<input type="hidden" name="gallery_id" value="<?=$image->val('image_gallery_id')?>" />
					<input type="submit" class="btn btn-danger btn-sm" value="<?=$this->t('Delete')?>" />
				</form>
			</div>
		</div>

		<div class="card-body p-2">
			<div class="text-center">
				<?php
					$this->z->images->renderImage($image->val('image_path'), 'mini');
				?>
			</div>
			<div class="btn-group mt-2" role="group" aria-label="Formats">
				<?php
					$original_size = $this->z->images->getImgSize($image->val('image_path'));
					$available_formats = ['mini', 'thumb', 'view', $this->z->images->original_format_name];
					$all_formats = $this->z->images->formats;
					foreach ($available_formats as $format_name) {
						$format = isset($all_formats[$format_name]) ? $all_formats[$format_name] : false;
						$width = $format ? $format['width'] : $original_size[0];
						$height = $format ? $format['height'] : $original_size[1];
						$mode = $format ? (isset($format['mode']) ? '(' . $format['mode'] . ')' : '(fit)') : '(orig)';
						$url = $this->z->images->img($image->val('image_path'), $format_name);
						?>
							<a tabindex="0"
								type="button"
								class="btn btn-sm btn-primary"
								data-bs-toggle="popover"
								data-bs-trigger="manual"
								data-bs-title="<?=$format_name?>: <?=$width?> x <?=$height?> <?=$mode?>"
								data-bs-content="<?=$url?>"
							>
								<?=$format_name?>
								<div class="d-none popover-content">
									<div class="d-flex align-items-center">
										<div>
											<?php
												$this->renderPartialView('value-to-clipboard', ['value' => $url]);
											?>
										</div>
										<div class="text-nowrap p-2"><?=$url?></div>
									</div>
								</div>
							</a>
						<?php
					}
				?>
			</div>
		</div>



	</div>
</div>
