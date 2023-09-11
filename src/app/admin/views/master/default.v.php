<!DOCTYPE html>
<html lang="<?=$this->z->i18n->selected_language->val('language_code') ?>">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

		<title><?=$this->getFullPageTitle() ?></title>
		<meta name="description" content="<?=$this->getConfigValue('site_description') ?>">
		<meta name="author" content="<?=$this->getConfigValue('site_author') ?>">

		<?php
			$this->renderIncludes('admin.head');
		?>

	</head>

	<body>
		<?php
			$this->renderAdminMenu();
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

		<?php
			$this->renderIncludes('admin.bottom');
		?>
	</body>
</html>
