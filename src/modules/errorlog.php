<?php

class errorlogModule extends zModule {

	public $path = 'error.log';
		
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
