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
			$this->renderCSSIncludes();
			$this->renderLESSIncludes();
			$this->renderJSIncludes_head();
		?>
				
	</head>

	<body>	
		<?php
			$this->renderAdminMenu();
			$this->renderMainView();
			$this->renderJSIncludes();			
		?>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

	</body>
</html>