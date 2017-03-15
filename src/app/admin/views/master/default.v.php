<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">		
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

		<meta name="description" content="zEngine default admin">
		<meta name="author" content="Karel Zavadil">

		<?php		
			$this->renderCSSIncludes();			
		?>
		
		<title><?=$this->data['site_title'] ?> - Administration - <?=$this->data['page_title'] ?></title>
	</head>

	<body>	
		<?php
			$this->renderAdminMenu();
			$this->renderMainView();
			$this->renderJSIncludes();			
		?>
	</body>
</html>