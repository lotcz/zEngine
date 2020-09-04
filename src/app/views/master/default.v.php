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

		<?php
			$this->renderIncludes('head');
		?>

	</head>

	<body>

		<?php
			$this->renderMainView();
			$this->renderIncludes('default');
		?>

		<?php
			$this->renderIncludes('bottom');
		?>
	</body>
</html>
