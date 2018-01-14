<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">		
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	
		<title><?=$this->getFullPageTitle() ?></title>
		<meta name="description" content="zEngine administration">
		<meta name="author" content="Karel Zavadil">

		<!-- BOOTSTRAP-->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap-theme.min.css">
		
		<?php		
			$this->renderIncludes('head');
		?>
				
	</head>

	<body>	
		<?php
			$this->renderAdminMenu();
			$this->renderMainView();
			$this->renderIncludes('default');
		?>
		
		<footer class="container-fluid spaced">
			<hr>
			<p>&copy; Karel Zavadil 2018. Z-engine v. <strong><?=$this->z->version ?></strong>. Application version <strong><?=$this->z->app->version ?></strong>.</p>
		</footer>
		
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

		<?php
			$this->renderIncludes('bottom');
		?>
	</body>
</html>