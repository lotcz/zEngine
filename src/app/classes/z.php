<?php

/**
* This class has various static methods that serve as useful helpers.
*/
class z {

	static $crlf = "\r\n";

	static function getClientIP() {
		 return $_SERVER['REMOTE_ADDR'];
	}

	/**
	* Redirect to a new URL.
	*/
	static function redirect($url = '', $statusCode = 303) {
		if (headers_sent()) {
			echo '<script>';
			echo "document.location = '$url';";
			echo '</script>';
			die();
		} else {
			header('Location: ' . $url, true, $statusCode);
			die();
		}
	}

	/**
	* If value can be interpreted as integer, then return it. Return null otherwise.
	* @return int
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
		return z::isMethod('POST');
	}

	static function isMethod($method) {
		return ($_SERVER['REQUEST_METHOD'] == $method);
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

	/**
	 * safely get ids array to prevent SQL injection
	*/
	static function getIntArray($name, $def = null) {
		$value = z::get($name);
		if (is_array($value)) {
			$str_array = $value;
		} else {
			if ($value === null || strlen($value) === 0) {
				return $def;
			}
			$str_array = explode(',', $value);
		}

		$result_array = [];
		foreach ($str_array as $str) {
			$result_array[] = z::parseInt($str);
		}
		return $result_array;
	}

	static function shorten($str, $len, $ellipsis = "...") {
		if (mb_strlen($str) > $len) {
			$length = $len - mb_strlen($ellipsis);
			return mb_substr($str, 0, $length) . $ellipsis;
		} else {
			return $str;
		}
	}

	static function trim($s, $chrs = ' ') {
		$result = trim($s, $chrs);
		if (strlen($result) === 0) {
			return null;
		}
		return $result;
	}

	static function trimSpecial($s) {
		return trim($s, ' .,-*/?!\'"');
	}

	static function trimSlashes($s) {
		return z::trim($s, '/');
	}

	static function splitString($str, $separators) {
		$results = [$str];
		foreach($separators as $separator) {
			$nresults = [];
			foreach($results as $result) {
				$arr = explode($separator, $result);
				$nresults = array_merge($nresults, $arr);
			}
			$results = $nresults;
		}
		return $results;
	}

	static function escapeSingleQuotes($str) {
		return str_replace('\'', '\\\'', $str);
	}

	static $czech_transliteration = [
		'á' => 'a', 'é' => 'e', 'ě' => 'e', 'í' => 'i', 'ý' => 'y', 'ó' => 'o', 'ú' => 'u', 'ů' => 'u', 'ž' => 'z', 'š' => 's', 'č' => 'c', 'ř' => 'r', 'ď' => 'd', 'ť' => 't', 'ň' => 'n',
		'Á' => 'A', 'É' => 'E', 'Ě' => 'E', 'Í' => 'I', 'Ý' => 'Y', 'Ó' => 'O', 'Ú' => 'U', 'Ů' => 'U', 'Ž' => 'Z', 'Š' => 'S', 'Č' => 'C', 'Ř' => 'R', 'Ď' => 'D', 'Ť' => 'T', 'Ň' => 'N'
	];

	static function transliterateCzech($str) {
		$result = $str;
		foreach (z::$czech_transliteration as $czech => $ascii) {
			$result = str_replace($czech, $ascii, $result);
		}
		return $result;
	}

	static function transliterate($str, $encoding = 'UTF-8') {
		return iconv($encoding, "ASCII//TRANSLIT", z::transliterateCzech($str));
	}

	static function slugify($str, $encoding = 'UTF-8') {
		$result = z::trimSpecial($str);
		$result = z::transliterate($result, $encoding);
		$result = mb_strtolower($result);
		$result = preg_replace("/[^a-zA-Z0-9\/_| -]/", '', $result);
		$result = preg_replace("/[_| -\/]+/", '-', $result);
		return $result;
	}

	/**
	* Convert mysql Datetime to php time (int)
	*/
	static function phpDatetime($mysqldate) {
		if (isset($mysqldate) && (strlen($mysqldate) > 0)) {
			return strtotime($mysqldate);
		} else {
			return null;
		}
	}

	/**
	* Convert php Datetime to mysql Datetime
	*/
	static function mysqlDatetime($time) {
		if (isset($time)) {
			return date('Y-m-d H:i:s', $time);
		} else {
			return null;
		}
	}

	/**
	* Convert php time to mysql Datetime
	*/
	static function mysqlTimestamp($time) {
		if (isset($time)) {
			return date('Y-m-d H:i:s', $time);
		} else {
			return null;
		}
	}

	static function formatDateForHtml($time) {
		if (isset($time) && $time != null) {
			return z::shorten(date_format(date_timestamp_set(new DateTime(), $time), 'c'), 19, "");
		} else {
			return '';
		}
	}

	static function getDbType($val) {
		if (is_int($val)) {
			return PDO::PARAM_INT;
		} else {
			return PDO::PARAM_STR;
		}
	}

	/**
	* Remove dangerous characters from string. Crucial for XSS protection.
	*/
	static function xssafe($data) {
		if (is_string($data)) {
			return z::stripHtmlTags($data);
		} else {
			return $data;
		}
	}

	static function startsWith($haystack, $needle)	{
		 return (mb_substr($haystack, 0, strlen($needle)) === $needle);
	}

	static function endsWith($haystack, $needle) {
		$length = mb_strlen($needle);
		return $length === 0 || (substr($haystack, -$length) === $needle);
	}

	static function contains($str, $sub) {
		return (mb_strpos($str, $sub) !== false);
	}

	static function debug($var) {
		var_dump($var);
		die();
	}

	static function dbg($var) {
		z::debug($var);
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

	/**
	* Convert all characters in a string to html entities.
	*/
	static function toHtmlEntities($string) {
		$convmap = array(0, 0xffffff, 0, 0xffffff);
		return mb_encode_numericentity($string, $convmap);
	}

	static function stripHtmlTags($text, $allowed_tags = '<br><i><b><p><strong>') {
		if (!$text) {
			return '';
		}
		return strip_tags($text, $allowed_tags);
	}

	/**
	* Takes seed array and replaces keys that exist in addition array with values from addition array.
	* Used for example when merging module config files.
	* @param $array_seed Array
	* @param $array_addition Array
	* @return Array
	*/
	static function mergeAssocArrays($array_seed, $array_addition) {
		return array_merge($array_seed, $array_addition);
	}

	/***
	 * select random element, remove it from the array and return it
	 */
	static function extractRandomElement(&$array) {
		if ($array === null || count($array) === 0) {
			return null;
		}
		$index = rand(0, count($array) - 1);
		$element = $array[$index];
		array_splice($array, $index, 1);
		return $element;
	}

	/**
	 * Return array of file names in given directory.
	 * @param $path
	 */
	static function listFiles($path) {
		return array_diff(scandir($path), array('.', '..'));
	}

	static function getExternalUrl($url) {
		if (strlen($url) > 0) {
			$url = strtolower($url);
			if (!z::startsWith($url, 'http')) {
				$url = 'http://' . $url;
			}
			return $url;
		}
		return null;
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
