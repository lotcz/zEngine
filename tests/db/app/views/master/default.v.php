<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">		
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

		<meta name="description" content="zEngine Core Test">
		<meta name="author" content="Karel Zavadil">

		<title>DB Test - <?=$this->data['page_title'] ?></title>
	</head>

	<body>		
		<?php
			$this->renderMainView();
		?>
		
		<p>
			<ul>
				<li>
					<a href="<?=$this->url() ?>">Home</a>
				</li>
				<li>
					<a href="<?=$this->url('select') ?>">Select</a>
				</li>
				<li>
					<a href="<?=$this->url('insert') ?>">Insert</a>
				</li>				
			</ul>
		</p>
	</body>
</html>