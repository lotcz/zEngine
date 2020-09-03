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

		<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">

		<?php
			$this->renderIncludes('head');
		?>

	</head>

	<body>

		<?php
			$this->renderMainView();
			$this->renderIncludes('default');
		?>

		<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
		<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
		<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>

		<?php
			$this->renderIncludes('bottom');
		?>
	</body>
</html>
