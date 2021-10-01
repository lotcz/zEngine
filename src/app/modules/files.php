<?php

/**
* Module that handles files. Mostly file uploading.
*/
class filesModule extends zModule {

	public $root_files_disk_path = '';
	public $root_files_url = '';

	function onEnabled() {
		$this->requireConfig();

		$this->root_files_disk_path = $this->getConfigValue('files_disk_path', $this->root_files_disk_path);
		$absolute = $this->getConfigValue('files_url_absolute', true);
		$files_url = $this->getConfigValue('files_url', $this->root_files_url);
		$this->root_files_url = ($absolute) ? $files_url : $this->z->url($files_url);
	}

	public function getFilePath($file) {
		return $this->root_files_disk_path . $file;
	}

	public function getFileURL($file) {
		if ($this->exists($file)) {
			return $this->root_files_url . '/' . $file;
		}
	}

	public function exists($file) {
		return file_exists($this->getFilePath($file));
	}

	public function deleteFile($file) {
		$original_path = $this->getFilePath($file);
		if (file_exists($original_path)) {
			unlink($original_path);
		}
	}

	public function uploadFile($name) {
		if (!(isset($_FILES[$name]) && strlen($_FILES[$name]['name']) > 0))
		{
			$this->z->messages->add('No uploaded file detected!', 'error');
			return null;
		}

		$filename_parts = pathinfo($_FILES[$name]['name']);
		$file_name = z::slugify($filename_parts['filename'], $this->z->core->default_encoding);
		$file_extension = $filename_parts['extension'];

		$target_path = $this->root_files_disk_path . '/';
		if (!is_dir($target_path)) {
			mkdir($target_path, 0777, true);
		}

		$file = $file_name . '.' . $file_extension;
		$i = 0;
		while ($this->exists($file)) {
			$i++;
			$file = $file_name . '_' . $i . '.' . $file_extension;
		}
		$target_file = $target_path . $file;

		if (!move_uploaded_file($_FILES[$name]['tmp_name'], $target_file)) {
			$this->z->messages->add(sprintf('Cannot upload file to %s', $target_file), 'error');
			return null;
		}

		return $file;
	}

}
