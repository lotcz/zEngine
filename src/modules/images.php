<?php 

class Images {

	static $formats = array('mini-thumb' => array( 'width' => 75, 'height' => 50 ),
							'thumb' => array( 'width' => 160, 'height' => 140 ),
							'view' => array( 'width' => 800, 'height' => 600 )
						);

	public $root_images_disk_path = '';
	public $root_images_url = '';

	function __construct( $data_path, $data_url) {
		$this->root_images_disk_path = $data_path;
		$this->root_images_url = $data_url;   
	}
	
	public function getImagePath( $image, $format = null ) {
		if (isset($format)) {
			$path_parts = pathinfo( $image );
			return $this->root_images_disk_path . $format . '/' . $path_parts['filename'] . '-' . $format . '.' . $path_parts['extension'];
		} else {
			return $this->root_images_disk_path . 'originals/' . $image;
		}
	}

	public function getImageURL( $image, $format = null ) {
		if ($this->exists($image, $format)) {
			if (isset($format)) {
				$path_parts = pathinfo( $image );
				return $this->root_images_url . '/' . $format . '/' . $path_parts['filename'] . '-' . $format . '.' . $path_parts['extension'];
			} else {
				return $this->root_images_url . '/originals/' . $image;
			}
		}
	}

	public function prepareImage( $image, $format = null ) {		
		if (!$this->exists($image, $format)) {
			if (!is_dir($this->root_images_disk_path . $format)) {
				mkdir($this->root_images_disk_path . $format, 0777, true);
			}
			$original_path = $this->getImagePath( $image );
			$resized_path = $this->getImagePath( $image, $format );
			
			if (file_exists($original_path)) {
				
				$info = getimagesize($original_path);
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

					default: //case 'image/jpeg':
						$image_create_func = 'imagecreatefromjpeg';
						$image_save_func = 'imagejpeg';
						$new_image_ext = 'jpg';
						break;							
				}
				
				$format_width = Images::$formats[$format]['width'];
				$format_height = Images::$formats[$format]['height'];
				
				$img = $image_create_func($original_path);				
				
				list($width, $height) = getimagesize($original_path);
								
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

				$tmp = imagecreatetruecolor($newWidth, $newHeight);
				
				switch ($new_image_ext)
					{
						case "png":
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
					
				imagecopyresampled($tmp, $img, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);

				if (file_exists($resized_path)) {
					unlink($resized_path);
				}
				$image_save_func($tmp, "$resized_path");

				imagedestroy($img);
				imagedestroy($tmp);
				
			} else {
				handleErr("Image $original_path not found. Cannot resize.", 'error');
			}
		}
	}

	public function deleteImageCache( $image ) {
		foreach (Images::$formats as $key => $format) {
			$resized_path = $this->getImagePath( $image, $key );
				if (file_exists($resized_path)) {
					unlink($resized_path);
			}
		}
	}
	
	public function deleteImage( $image ) {
		$this->deleteImageCache($image);
		$original_path = $this->getImagePath( $image );
		if (file_exists($original_path)) {
			unlink($original_path);
		}
	}

	public function exists( $image, $format = null ) {
		$original_path = $this->getImagePath( $image, $format );
		return file_exists($original_path);
	}
	
	public function img( $image, $format = null ) {
		$this->prepareImage( $image, $format );
		return $this->getImageURL( $image, $format );
	}
	
	public function renderImage($image, $format = 'thumb', $alt = '', $css = '') {
		if (isset($image) && strlen($image) > 0) {
			$url = $this->img($image, $format);			
		}
		if (!isset($url)) {
			$url = $this->img('no-image.png', $format);
		}
		
		echo sprintf('<img src="%s" class="%s" alt="%s" />', $url, $css, $alt);
	}
	
	public function uploadImage($name) {
		global $messages;
		$image = null;
		if (isset($_FILES[$name]) && strlen($_FILES[$name]['name']) > 0) {
			$image = basename($_FILES[$name]['name']);
			$target_file = $this->root_images_disk_path . '/originals/' . $image;
			$uploadOk = true;
			$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
			
			// Check if image file is a actual image or fake image		
			$check = getimagesize($_FILES[$name]['tmp_name']);
			if($check !== false) {
				$uploadOk = true;
			} else {
				$uploadOk = false;
				$messages->add('File is not image.', 'warning');
			}
			
			 if (move_uploaded_file($_FILES[$name]['tmp_name'], $target_file)) {
				$uploadOk = true;
			} else {
				$uploadOk = false;
				$messages->add(sprintf('Cannot upload image to %s',$target_file), 'warning');
			}
		}
		
		return ($uploadOk) ? $image : null;
	}
}