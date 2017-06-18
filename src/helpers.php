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

/*
	TOKEN GENERATOR
	
	example: $token = generateToken(10);
	-- now $token is something like '9HuE48ErZ1'
*/
function getRandomNumber() {
	return rand(0,9);
}

function getRandomLowercase() {
	return chr(rand(97,122));
}

function getRandomUppercase() {
	return strtoupper(getRandomLowercase());
}	

function generateToken($len) {
	$s = '';
	for ($i = 0; $i < $len; $i++) {
		$case = rand(0,2);
		if ($case == 0) {
			$s .= getRandomNumber();
		} elseif ($case == 1) {
			$s .= getRandomUppercase();
		} else {
			$s .= getRandomLowercase();
		}
	}
	return $s;
}

function dbg($var) {
	var_dump($var);
	die();
}

/*
	ZIP
*/

function unzip($zipfile) {
	$zip = zip_open($zipfile);
	while ($zip_entry = zip_read($zip))    {
		zip_entry_open($zip, $zip_entry);
		if (substr(zip_entry_name($zip_entry), -1) == '/') {
			$zdir = substr(zip_entry_name($zip_entry), 0, -1);
			if (file_exists($zdir)) {
				trigger_error('Directory "<b>' . $zdir . '</b>" exists', E_USER_ERROR);
				return false;
			}
			mkdir($zdir);
		}
		else {
			$name = zip_entry_name($zip_entry);
			if (file_exists($name)) {
				trigger_error('File "<b>' . $name . '</b>" exists', E_USER_ERROR);
				return false;
			}
			$fopen = fopen($name, "w");
			fwrite($fopen, zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)), zip_entry_filesize($zip_entry));
		}
		zip_entry_close($zip_entry);
	}
	zip_close($zip);
	return true;
}