<?php

class zZip {

	static function unzip($zipfile) {
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

	static function zipFolder($folder, $archive_name) {
		// Get real path for our folder
		$rootPath = realpath($folder);

		// Initialize archive object
		$zip = new ZipArchive();
		$zip->open($archive_name, ZipArchive::CREATE | ZipArchive::OVERWRITE);

		// Create recursive directory iterator
		/** @var SplFileInfo[] $files */
		$files = new RecursiveIteratorIterator(
		    new RecursiveDirectoryIterator($rootPath),
		    RecursiveIteratorIterator::LEAVES_ONLY
		);

		foreach ($files as $name => $file)
		{
		    // Skip directories (they would be added automatically)
		    if (!$file->isDir())
		    {
		        // Get real and relative path for current file
		        $filePath = $file->getRealPath();
		        $relativePath = substr($filePath, strlen($rootPath) + 1);

		        // Add current file to archive
		        $zip->addFile($filePath, $relativePath);
		    }
		}

		// Zip archive will be created only after closing object
		$zip->close();
	}

}
