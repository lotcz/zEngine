<?php

/**
* Module that handles static zEngine resources like CSS and javascript.
*/
class resourcesModule extends zModule {

	//url base for all resources
	public $base_url = 'resources';
	
	//base dir for all resources relative to this file
	public $base_dir = '/../app/resources/';
	
	static function getContentType($ext) {
		switch ($ext) {
			case 'css':
				return 'text/css';
				break;
			case 'js':
				return 'application/javascript';
				break;
			default:
				return '';
		}
	}
	
	public function onInit() {
		if ($this->z->core->getPath(0) == $this->base_url) {
			$resource_file = $this->z->core->getPath(1);
			if ($this->z->core->pathExists(2)) {
				$resource_file .= '/' . $this->z->core->getPath(2);
			}
			$resource_path = __DIR__ . $this->base_dir . $resource_file;
			if (file_exists($resource_path)) {
				$path_parts = pathinfo($resource_file);
				header('Content-Description: File Transfer');
				header('Content-Type: ' . Self::getContentType($path_parts['extension']));
				header('Content-Disposition: attachment; filename="' . $resource_file . '"');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: ' . filesize($resource_path));
				readfile($resource_path);
				exit;
			} else {
				die("Resource file $resource_path not found!");
			}
		}
	}

}