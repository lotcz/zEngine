<!DOCTYPE html>
<html lang="<?=$this->z->i18n->selected_language->val('language_code') ?>">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

		<title><?=$this->getFullPageTitle() ?></title>
		<meta name="description" content="<?=$this->getConfigValue('site_description') ?>">
		<meta name="author" content="<?=$this->getConfigValue('site_author') ?>">
		<meta name="keywords" content="<?=$this->getConfigValue('site_keywords') ?>,<?=$this->getPageKeywords() ?>">

		<meta property="og:site_name" content="<?=$this->getConfigValue('site_title') ?>" />
		<meta property="og:title" content="<?=$this->getFullPageTitle() ?>" />
		<meta property="og:description" content="<?=$this->getConfigValue('site_description') ?>" />

		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" integrity="sha384-WskhaSGFgHYWDcbwN70/dfYBj47jz9qbsMId/iRN3ewGhXQFZCSftd1LZCfmhktB" crossorigin="anonymous">

		<?php
			$this->renderIncludes('head');
		?>

	</head>

	<body>

		<?php
			$this->renderMainView();
			$this->renderIncludes('default');
		?>

		<script
			  src="https://code.jquery.com/jquery-3.3.1.min.js"
			  integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8="
			  crossorigin="anonymous"></script>
		<script
				src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"
				integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
				crossorigin="anonymous"></script>
		<script
				src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"
				integrity="sha384-smHYKdLADwkXOn1EmN1qk/HfnUcbVRZyYmZ4qpPea6sjB/pTJ0euyQp0Mk8ck+5T"
				crossorigin="anonymous"></script>

		<?php
			$this->renderIncludes('bottom');
		?>
	</body>
</html>
