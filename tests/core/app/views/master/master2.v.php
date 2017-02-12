<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">		
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->

		<meta name="description" content="zEngine Core Test">
		<meta name="author" content="Karel Zavadil">

		<title><?=$this->data['site_title'] ?> - <?=$this->data['page_title'] ?></title>
	</head>

	<body>		
		<h1>Hello World!</h1>

		<p>This is an alternative master view template in <strong>views/master/master2.v.php</strong></p>

		<?php

			$this->renderMainView();

		?>
		
		<p>
			<ul>
				<li>
					<a href="<?=$this->url() ?>">Home</a>
				</li>				
			</ul>
		</p>
	</body>
</html>