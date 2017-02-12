<?php

/*

	zEngine's Little Helpers

*/

function parseInt($val) {		
	if (isset($val) && strlen(trim($val)) > 0) {
		return intval($val);
	} else {
		return null;
	}
}

function parseFloat($val) {		
	if (isset($val) && strlen(trim($val)) > 0) {
		return floatval($val);
	} else {
		return null;
	}
}

function isPost() {
	return ($_SERVER['REQUEST_METHOD'] === 'POST');
}

function get($name, $def = null) {
	return isset($_GET[$name]) ? $_GET[$name] : (isset($_POST[$name]) ? $_POST[$name] : $def);
}

function getInt($name, $def = null) {
	return parseInt(get($name, $def));
}

function getFloat($name, $def = null) {
	return parseFloat($this->get($name, $def));
}
	
function customTrim($s, $chrs = '.,-*/1234567890') {				
	do {
		$trimmed = false;
		$s = trim($s);
		if (strlen($s)) {
			for ($i = 0, $max = strlen($chrs); $i < $max; $i++) {
				if ($s[0] == $chrs[$i]) {
					$s = substr($s,1,strlen($s)-1);
					$trimmed = true;
				}
				if ($s[strlen($s)-1] == $chrs[$i]) {
					$s = substr($s,0,strlen($s)-1);
					$trimmed = true;
				}
			}
		}
	} while ($trimmed);		
	
	return $s;
}

/*
	remove slashes if they are present at first or last character of the string
*/
function trimSlashes($s) {		
	return customTrim($s, '/');
}