<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">		
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

		<meta name="description" content="z">
		<meta name="author" content="Karel Zavadil">

		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css">
		<link rel="stylesheet" href="<?=$this->url('style.css') ?>">
		
		<?php		
			$this->renderIncludes('head');			
		?>
		
		<title><?=$this->data['site_title'] ?> - <?=$this->data['page_title'] ?></title>
	</head>

	<body>
		
		<?php
			$this->renderMainView();
			$this->renderIncludes('default');			
		?>
		
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/js/bootstrap.min.js"></script>

		<?php			
			$this->renderIncludes('bottom');
		?>
	</body>
</html>