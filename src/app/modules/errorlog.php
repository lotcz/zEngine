<?php

/**
* Module that handles writing into error log file.
*/
class errorlogModule extends zModule {

	public $path = './zEngine.log';

	public function onEnabled() {
		$this->path = $this->getConfigValue('error_log_path', $this->path);
	}

	public function write($args) {
		$args = func_get_args();
		foreach ($args as $arg) {
			if (is_array($arg)) {
				foreach ($arg as $ar) {
					$this->write($ar);
				}
				return;
			}
			$myfile = fopen($this->path, 'a');
			fwrite($myfile, sprintf('%s: %s', date('Y-m-d H:i:s'), var_export($arg, true) . PHP_EOL));
			fclose($myfile);
		}
	}

	public function rewrite($message = null) {
		$myfile = fopen($this->path, 'w');
		fwrite($myfile, $message);
		fclose($myfile);
	}

}
