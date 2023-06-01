<?php

/**
* Module that handles images. Mostly image uploading and resizing.
*/
class imagesModule extends zModule {

	public $formats = '';
	public $root_images_disk_path = '';
	public $root_images_url = '';

	public $no_image = 'no-image.jpg';
	public $image_not_found = 'image-not-found.jpg';

	function onEnabled() {
		$this->requireConfig();

		$this->root_images_disk_path = $this->getConfigValue('images_disk_path', $this->root_images_disk_path);
		$this->root_images_url = $this->config['images_url'];
		$this->formats = $this->config['formats'];
		$this->no_image = $this->getConfigValue('no_image', $this->no_image);
		$this->image_not_found = $this->getConfigValue('image_not_found', $this->image_not_found);
	}

	public function getImagePath($image, $format = null) {
		if (!isset($format)) {
			return $this->getImagePath($image, 'originals');
		}
		return $this->root_images_disk_path . $format . '/' . $image;
	}

	public function getImageURL($image, $format = null) {
		if (!isset($format)) {
			return $this->getImageURL($image, 'originals');
		}
		return $this->root_images_url . '/' . $format . '/' . $image;
	}

	public function prepareImage($image, $format = null ) {
		if (!isset($this->formats[$format])) {
			$message = sprintf('Preparing \'%s\'. Format \'%s\' doesn\'t exist.', $image, $format);
			$this->z->errorlog->write($message);
			$this->z->messages->error($message);
			return;
		}

		if (!$this->exists($image, $format)) {
			$original_path = $this->getImagePath($image);
			$resized_path = $this->getImagePath($image, $format);
			$resized_dir = pathinfo($resized_path)['dirname'];
			if (!is_dir($resized_dir)) {
				mkdir($resized_dir, 0777, true);
			}

			if (file_exists($original_path)) {
				$info = getimagesize($original_path);
				if ((!isset($info[0])) || (!isset($info[1]))) {
					$this->z->errorlog->write(sprintf('Image %s has incomplete info: [%s].', $image, implode(',', $info)));
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

				try {
					$img = $image_create_func($original_path);
				} catch (Exception $e) {
					$message = sprintf('Creating image \'%s\' failed: %s', $image, $e->getMessage());
					$this->z->errorlog->write($message);
					$this->z->messages->error($message);
					return;
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

				switch ($new_image_ext)	{
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

				imagecopyresampled($tmp, $img, 0, 0, $src_x, $src_y, round($newWidth), round($newHeight), $src_width, $src_height);

				if (file_exists($resized_path)) {
					unlink($resized_path);
				}
				$image_save_func($tmp, "$resized_path");

				imagedestroy($img);
				imagedestroy($tmp);

			} else {
				$message = "Image original $original_path not found. Cannot resize.";
				$this->z->errorlog->write($message);
				$this->z->messages->error($message);
			}
		}
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
			$original_path = $this->getImagePath( $image );
			if (file_exists($original_path)) {
				unlink($original_path);
			}
		}
	}

	public function exists($image, $format = null ) {
		if (!(isset($image) && strlen($image) > 0)) {
			return false;
		}
		return file_exists($this->getImagePath($image, $format));
	}

	public function img($image, $format = null) {
		if (!(isset($image) && strlen($image) > 0)) {
			$image = $this->no_image;
		}
		if (!$this->exists($image)) {
			$image = $this->image_not_found;
		}
		if ($format !== null) {
			$this->prepareImage($image, $format);
		}
		return $this->getImageURL($image, $format);
	}

	public function getImgSize($image, $format) {
		$path = '';
		if ($image === null) {
			$path = $this->getImagePath($this->no_image, $format);
		} else if ($this->exists($image, $format)) {
			$path = $this->getImagePath($image, $format);
		} else {
			$path = $this->getImagePath($this->image_not_found, $format);
		}
		$info = getimagesize($path);
		return $info[3];
	}

	public function renderImage($image, $format = 'thumb', $alt = '', $css = '') {
		$url = $this->img($image, $format);
		$size = $this->getImgSize($image, $format);
		echo sprintf('<img src="%s" class="%s" alt="%s" %s />', $url, $css, $alt, $size);
	}

	public function uploadImage($name) {
		if (!(isset($_FILES[$name]) && strlen($_FILES[$name]['name']) > 0))
		{
			$this->z->messages->add('No uploaded file detected!', 'error');
			return null;
		}

		$filename_parts = pathinfo($_FILES[$name]['name']);
		$file_name = z::slugify($filename_parts['filename'], $this->z->core->default_encoding);
		$file_extension = $filename_parts['extension'];

		$target_path = $this->root_images_disk_path . '/originals/';
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
		$uploadOk = true;

		// Check if image file is an actual image
		$check = getimagesize($_FILES[$name]['tmp_name']);
		if($check === false) {
			$this->z->messages->add('Uploaded file is not an image!', 'error');
			return null;
		}

		if (!move_uploaded_file($_FILES[$name]['tmp_name'], $target_file)) {
			$this->z->messages->add(sprintf('Cannot upload image to %s', $target_file), 'error');
			return null;
		}

		return $image;
	}
}
