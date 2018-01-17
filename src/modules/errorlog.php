<?php

/**
* Module that handles writing into error log file.
*/
class errorlogModule extends zModule {

	public $path = '/var/log/zengine.log';

	public function onEnabled() {
		$this->path = $this->getConfigValue('error_log_path', $this->path);
	}

	public function write($message) {
		$myfile = fopen($this->path, 'a');
		fwrite($myfile, sprintf('%s: %s', date('Y-m-d H:i:s'), $message . PHP_EOL));
		fclose($myfile);
	}

	public function rewrite($message = null) {
		$myfile = fopen($this->path, 'w');
		fwrite($myfile, $message);
		fclose($myfile);
	}

}
