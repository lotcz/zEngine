<?php

/**
* Module that handles static zEngine resources like CSS and javascript.
*/
class resourcesModule extends zModule {

	//url base for all resources
	public $base_url = 'resources';

	//base dir for all resources relative to this file
	public $base_dir = '/../resources/';

	static function getContentType($ext) {
		switch ($ext) {
			case 'css':
				return 'text/css';
				break;
			case 'js':
				return 'application/javascript';
				break;
			case 'svg':
				return 'image/svg+xml';
				break;
			case 'jpg':
				return 'image/jpg';
				break;
			default:
				return '';
		}
	}

	public function onBeforeInit() {
		if ($this->z->core->getPath(0) == $this->base_url) {
			$resource_file = $this->z->core->getPath(1);
			$i = 2;
			while ($this->z->core->pathExists($i)) {
				$resource_file .= '/' . $this->z->core->getPath($i);
				$i++;
			}
			$resource_path = $this->z->app_dir . 'resources/' . $resource_file;
			if (!file_exists($resource_path)) {
				$resource_path = __DIR__ . $this->base_dir . $resource_file;
			}
			if (file_exists($resource_path) && !is_dir($resource_path)) {
				$Etag = filemtime($resource_path);
				if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && ($Etag == $_SERVER['HTTP_IF_NONE_MATCH'])) {
					http_response_code(304);
					header('Cache-Control: max-age=' . $this->getConfigValue('default_cache_age', 120));
					header('ETag: ' . $Etag);
					exit;
				} else {
					$path_parts = pathinfo($resource_file);
					header('Content-Description: File Transfer');
					header('Content-Type: ' . self::getContentType($path_parts['extension']));
					header('Content-Disposition: attachment; filename="' . $resource_file . '"');
					header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + $this->getConfigValue('default_cache_age', 120)));
					header('Cache-Control: max-age=' . $this->getConfigValue('default_cache_age', 120));
					header('ETag: ' . $Etag);
					header('Pragma: public');
					header('Content-Length: ' . filesize($resource_path));
					readfile($resource_path);
					exit;
				}
			} else {
				http_response_code(404);
				die("Resource file $resource_path not found!");
			}
		}
	}

}
