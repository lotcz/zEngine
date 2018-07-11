<?php

/**
* This class has various static methods that serve as useful helpers.
*/
class z {

	/**
	* Redirect to a new URL.
	*/
	static function redirect($url = '', $statusCode = 303) {
		header('Location: ' . $url, true, $statusCode);
		die();
	}

	/**
	* If value can be interpreted as integer, then return it. Return null otherwise.
	*/
	static function parseInt($val) {
		if (isset($val) && strlen(trim($val)) > 0) {
			return intval($val);
		} else {
			return null;
		}
	}

	/**
	* If value can be interpreted as floating point decimal number, then return it. Return null otherwise.
	*/
	static function parseFloat($val) {
		if (isset($val) && strlen(trim($val)) > 0) {
			return floatval($val);
		} else {
			return null;
		}
	}

	/**
	* Divide two numbers, but never raise an exception. Zero is returned when dividing by zero, null is returned when one of numbers is null.
	*/
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
	
	/**
	* Return true if current http request is POST which in most cases means that a form was submitted.
	*/
	static function isPost() {
		return ($_SERVER['REQUEST_METHOD'] === 'POST');
	}

	/**
	* Return value from $_GET if set, or from $_POST if set, or the default if none of them.
	*/
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

	/**
	* Remove dangerous characters from string. Crucial for XSS protection.
	*/
	static function xssafe($data, $encoding = 'UTF-8') {
	   return htmlspecialchars($data, ENT_QUOTES | ENT_HTML401, $encoding);
	}
	
	static function debug($var) {
		var_dump($var);
		die();
	}
	
	static function formatForJS($value) {
		return json_encode($value);		
	}
	
	static function createHash($value) {
		return password_hash($value, PASSWORD_DEFAULT);
	}

	static function verifyHash($value, $hash) {
		return password_verify($value, $hash);
	}
	
	/*
		TOKEN GENERATOR

		example: $token = generateRandomToken(10);
		-- now $token is something like '9HuE48ErZ1'
	*/
	
	/**
	* Return random number between 0 and 9.
	*/	
	static function getRandomNumber() {
		return rand(0,9);
	}

	/**
	* Return random lowercase character.
	*/	
	static function getRandomLowercase() {
		return chr(rand(97,122));
	}

	/**
	* Return random uppercase character.
	*/	
	static function getRandomUppercase() {
		return strtoupper(Self::getRandomLowercase());
	}

	/**
	* Return string of random numbers, uppercase and lowercase characters.
	*/	
	static function generateRandomToken($len) {
		$s = '';
		for ($i = 0; $i < $len; $i++) {
			$case = rand(0,2);
			if ($case == 0) {
				$s .= Self::getRandomNumber();
			} elseif ($case == 1) {
				$s .= Self::getRandomUppercase();
			} else {
				$s .= Self::getRandomLowercase();
			}
		}
		return $s;
	}
}
