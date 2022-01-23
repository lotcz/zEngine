<!DOCTYPE html>
<html lang="<?=$this->z->i18n->selected_language->val('language_code') ?>">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

		<title><?=$this->getFullPageTitle() ?></title>
		<meta name="description" content="<?=$this->getConfigValue('site_description') ?>">
		<meta name="author" content="<?=$this->getConfigValue('site_author') ?>">

		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">

		<?php
			$this->renderIncludes('admin.head');
		?>

	</head>

	<body>
		<?php
			$this->renderAdminMenu();
			$this->renderIncludes('admin.top');
			$this->renderMainView();
			$this->renderIncludes('admin.default');
		?>

		<footer class="container-fluid spaced">
			<hr>
			<p>
				Application <a href="<?=$this->url('') ?>"><?=$this->z->core->getConfigValue('site_title')?></a> version <strong><?=$this->z->core->app_version ?></strong><br>
 				zEngine version <strong><?=$this->z->version ?></strong><br>
				&copy; Karel Zavadil <?=date('Y')?>
			</p>
		</footer>

		<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>

		<?php
			$this->renderIncludes('admin.bottom');
		?>
	</body>
</html>
