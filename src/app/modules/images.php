<?php

/**
 * Module that handles images. Mostly image uploading and resizing.
 */
class imagesModule extends zModule {

	public array $depends_on = ['files'];

	public $formats = [];
	public $root_images_disk_path = '';
	public $root_images_url = '';

	public $original_format_name = 'original';
	public $no_image = 'no-image.jpg';
	public $image_not_found = 'image-not-found.jpg';
	public $invalid_image = 'invalid-image.jpg';
	public $max_upload_size = null;

	function onEnabled() {
		$this->requireConfig();

		$this->root_images_disk_path = $this->getConfigValue('images_disk_path', $this->root_images_disk_path);
		$this->root_images_url = $this->getConfigValue('images_url');
		$this->formats = $this->getConfigValue('formats');
		$this->original_format_name = $this->getConfigValue('original_format_name', $this->original_format_name);
		$this->no_image = $this->getConfigValue('no_image', $this->no_image);
		$this->image_not_found = $this->getConfigValue('image_not_found', $this->image_not_found);
		$this->invalid_image = $this->getConfigValue('invalid_image', $this->invalid_image);
		$this->max_upload_size = $this->getConfigValue('max_upload_size', $this->max_upload_size);
	}

	public function getImagePath($image, $format = null) {
		if (empty($format)) {
			return $this->getImagePath($image, $this->original_format_name);
		}
		return $this->root_images_disk_path . $format . '/' . $image;
	}

	public function getImageURL($image, $format = null) {
		if (empty($format)) {
			return $this->getImageURL($image, $this->original_format_name);
		}
		return $this->root_images_url . '/' . $format . '/' . $image;
	}

	public function prepareImage($image, $format = null) {
		if (empty($format) || $format === $this->original_format_name) {
			return $this->getImageURL($image, $format);
		}

		if (!isset($this->formats[$format])) {
			$message = sprintf('Preparing \'%s\'. Format \'%s\' doesn\'t exist.', $image, $format);
			$this->z->errorlog->write($message);
			$this->z->messages->error($message);
			return null;
		}

		if (!$this->exists($image, $format)) {
			$original_path = $this->getImagePath($image);
			$resized_path = $this->getImagePath($image, $format);
			$resized_dir = pathinfo($resized_path)['dirname'];
			if (!is_dir($resized_dir)) {
				mkdir($resized_dir, 0777, true);
			}

			if (!file_exists($original_path)) {
				$message = "Image original $original_path not found. Cannot resize.";
				$this->z->errorlog->write($message);
				$this->z->messages->error($message);
				return null;
			}

			$info = $this->getImgSize($image);
			if (empty($info)) {
				$this->z->errorlog->write(sprintf('Image %s has no info', $image));
				return null;
			}
			if ((!isset($info[0])) || (!isset($info[1]))) {
				$this->z->errorlog->write(sprintf('Image %s has incomplete info: %s.', $image, print_r($info, true)));
				return null;
			}
			$mime = $info['mime'];

			switch ($mime) {
				case 'image/png':
					$image_create_func = 'imagecreatefrompng';
					$image_save_func = 'imagepng';
					$new_image_ext = 'png';
					break;

				case 'image/gif':
					$image_create_func = 'imagecreatefromgif';
					$image_save_func = 'imagegif';
					$new_image_ext = 'gif';
					break;

				case 'image/webp':
					$image_create_func = 'imagecreatefromwebp';
					$image_save_func = 'imagewebp';
					$new_image_ext = 'webp';
					break;

				default: //case 'image/jpeg':
					$image_create_func = 'imagecreatefromjpeg';
					$image_save_func = 'imagejpeg';
					$new_image_ext = 'jpg';
					break;
			}

			$format_conf = $this->formats[$format];
			$format_width = $format_conf['width'];
			$format_height = $format_conf['height'];
			$format_mode = isset($format_conf['mode']) ? $format_conf['mode'] : 'fit';

			/* check file size */
			if ($this->max_upload_size !== null) {
				$size = filesize($original_path);
				if (is_numeric($size) && $size > $this->max_upload_size) {
					$this->z->errorlog->write("Original image $original_path has size $size which exceeds maximum allowed size $this->max_upload_size");
					return null;
				}
			}

			if ($this->z->isDebugMode()) {
				$this->z->errorlog->write("Resizing image $original_path ($format_width x $format_height, $format_mode, $new_image_ext)");
			}

			try {
				$img = @$image_create_func($original_path);
			} catch (Throwable $e) {
				$message = sprintf('Error when resizing %s to format %s: %s', $original_path, $format, $e->getMessage());
				$this->z->errorlog->write($message);
				$this->z->messages->error($message);
				return null;
			}

			$width = z::parseInt($info[0]);
			$height = z::parseInt($info[1]);

			$src_x = 0;
			$src_y = 0;
			$src_width = $width;
			$src_height = $height;

			switch ($format_mode) {
				case 'scale':
					$newHeight = $format_height;
					$newWidth = $format_width;
					break;

				case 'crop':
					$original_aspect = $width / $height;
					$new_aspect = $format_width / $format_height;

					if ($original_aspect > $new_aspect) {
						$src_width = $height * $new_aspect;
						$src_x = ($width - $src_width) / 2;
					} else {
						$src_height = $width / $new_aspect;
						$src_y = ($height - $src_height) / 2;
					}

					$newHeight = $format_height;
					$newWidth = $format_width;

					break;

				case 'fit':
				default:
					if ($width > $format_width) {
						$newHeight = ($height / $width) * $format_width;
						$newWidth = $format_width;
					} else {
						$newHeight = $height;
						$newWidth = $width;
					}

					if ($newHeight > $format_height) {
						$newWidth = ($newWidth / $newHeight) * $format_height;
						$newHeight = $format_height;
					}
					break;
			}

			$tmp = imagecreatetruecolor(round($newWidth), round($newHeight));

			switch ($new_image_ext) {
				case "png":
				case "webp":

					// integer representation of the color black (rgb: 0,0,0)
					$background = imagecolorallocate($tmp, 0, 0, 0);

					// removing the black from the placeholder
					imagecolortransparent($tmp, $background);

					// turning off alpha blending (to ensure alpha channel information
					// is preserved, rather than removed (blending with the rest of the
					// image in the form of black))
					imagealphablending($tmp, false);

					// turning on alpha channel information saving (to ensure the full range
					// of transparency is preserved)
					imagesavealpha($tmp, true);

					break;
				case "gif":

					// integer representation of the color black (rgb: 0,0,0)
					$background = imagecolorallocate($tmp, 0, 0, 0);

					// removing the black from the placeholder
					imagecolortransparent($tmp, $background);

					break;
			}

			imagecopyresampled(
				$tmp,
				$img,
				0,
				0,
				intval($src_x),
				intval($src_y),
				intval(round($newWidth)),
				intval(round($newHeight)),
				intval($src_width),
				intval($src_height)
			);

			if (file_exists($resized_path)) {
				unlink($resized_path);
			}
			$image_save_func($tmp, "$resized_path");

			imagedestroy($img);
			imagedestroy($tmp);


		}

		return $this->getImageURL($image, $format);
	}

	public function deleteImageCache($image) {
		foreach ($this->formats as $key => $format) {
			$resized_path = $this->getImagePath($image, $key);
			if (file_exists($resized_path)) {
				unlink($resized_path);
			}
		}
	}

	public function deleteImage($image) {
		if ($image !== null && strlen($image) > 0) {
			$this->deleteImageCache($image);
			$original_path = $this->getImagePath($image);
			if (file_exists($original_path)) {
				unlink($original_path);
			}
		}
	}

	public function exists($image, $format = null) {
		if (empty($image)) {
			return false;
		}
		return file_exists($this->getImagePath($image, $format));
	}

	public function isValidImage($image, $format = null) {
		return $this->getImgSize($image, $format) !== null;
	}

	public function getImageForRendering($image, $format = null) {
		if (!(isset($image) && strlen($image) > 0)) {
			$image = $this->no_image;
		} else if (!$this->exists($image)) {
			$image = $this->image_not_found;
		} else if (!$this->isValidImage($image)) {
			$image = $this->invalid_image;
		}
		$url = $this->prepareImage($image, $format);
		if (empty($url)) {
			$image = $this->invalid_image;
			$this->prepareImage($image, $format);
		}
		return $image;
	}

	public function img($image, $format = null) {
		$image = $this->getImageForRendering($image, $format);
		return $this->getImageURL($image, $format);
	}

	public function getImgSize($image, $format = null) {
		$path = $this->getImagePath($image, $format);
		try {
			$size = @getimagesize($path);
		} catch (Exception $e) {
			$this->z->errorlog->write(sprintf("Error when reading image size of '%s': %s", $path, $e->getMessage()));
		}
		return isset($size) ? $size : null;
	}

	public function getImgSizeAttr($image, $format = null) {
		$size = $this->getImgSize($image, $format);
		return empty($size) ? null : $size[3];
	}

	public function renderImage($image, $format = 'thumb', $alt = '', $css = '') {
		$image = $this->getImageForRendering($image, $format);
		$size = $this->getImgSizeAttr($image, $format);
		echo sprintf('<img src="%s" class="%s" alt="%s" %s />', $this->img($image, $format), $css, $alt, $size);
	}

	private function uploadImageInternal($file_input) {

		/* check file size */
		if ($this->max_upload_size !== null) {
			$size = $file_input['size'];
			if (is_numeric($size) && $size > $this->max_upload_size) {
				$this->z->messages->add("Uploaded image size $size exceeds maximum allowed size $this->max_upload_size");
				return null;
			}
		}

		$filename_parts = pathinfo($file_input['name']);
		$file_name = z::slugify($filename_parts['filename'], $this->z->core->default_encoding);
		$file_extension = $filename_parts['extension'];

		$target_path = $this->root_images_disk_path . '/' . $this->original_format_name . '/';
		if (!is_dir($target_path)) {
			mkdir($target_path, 0777, true);
		}

		$image = $file_name . '.' . $file_extension;
		$i = 0;
		while ($this->exists($image)) {
			$i++;
			$image = $file_name . '_' . $i . '.' . $file_extension;
		}
		$target_file = $target_path . $image;

		if ($file_input['error'] !== UPLOAD_ERR_OK) {
			$this->z->messages->add(sprintf('File upload error: %s', $file_input['error']), 'error');
			return null;
		}

		// Check if image file is an actual image
		$check = getimagesize($file_input['tmp_name']);
		if ($check === false) {
			$this->z->messages->add('Uploaded file is not an image!', 'error');
			return null;
		}

		if (!move_uploaded_file($file_input['tmp_name'], $target_file)) {
			$this->z->messages->add(sprintf('Cannot upload image to %s', $target_file), 'error');
			return null;
		}

		return $image;
	}

	public function uploadImage($file_input_name) {
		if (!isset($_FILES[$file_input_name])) {
			$this->z->messages->add('No uploaded file detected!', 'error');
			return null;
		}

		$file_input = $_FILES[$file_input_name];

		if (empty($file_input['name'])) {
			$this->z->messages->add('No uploaded file name detected!', 'error');
			return null;
		}

		if (is_array($file_input['name'])) {
			$results = [];
			$file_inputs = $this->z->files->reArrayFiles($file_input);
			foreach ($file_inputs as $input) {
				$results[] = $this->uploadImageInternal($input);
			}
			return $results;
		}

		return $this->uploadImageInternal($file_input);
	}
}
