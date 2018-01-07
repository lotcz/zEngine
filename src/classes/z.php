<?php

class z {

	static function redirect($url = '', $statusCode = 303) {
		header('Location: ' . $url, true, $statusCode);
		die();
	}

	static function parseInt($val) {
		if (isset($val) && strlen(trim($val)) > 0) {
			return intval($val);
		} else {
			return null;
		}
	}

	static function parseFloat($val) {
		if (isset($val) && strlen(trim($val)) > 0) {
			return floatval($val);
		} else {
			return null;
		}
	}

	static function safeDivide($a, $b) {
		if (isset($a) && isset($b)) {
			if ($b == 0) {
				return 0;
			} else {
				return $a / $b;
			}			
		} else {
			return null;
		}
	}
	
	static function isPost() {
		return ($_SERVER['REQUEST_METHOD'] === 'POST');
	}

	static function get($name, $def = null) {
		return isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? $_POST[$name] : $def);
	}

	static function getInt($name, $def = null) {
		return z::parseInt(z::get($name, $def));
	}

	static function getFloat($name, $def = null) {
		return z::parseFloat(z::get($name, $def));
	}

	static function trim($s, $chrs = ' .,-*/1234567890') {
		return trim($s, $chrs);
	}

	static function trimSlashes($s) {
		return z::trim($s, '/');
	}

	static function escapeSingleQuotes($str) {
		return str_replace('\'', '\\\'', $str);
	}

	static function debug($var) {
		var_dump($var);
		die();
	}
	
	static function formatForJS($value) {
		return json_encode($value);		
	}
}
