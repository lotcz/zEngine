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

		<p>This is a test site for zEngine core. This paragraph is in master view template in <strong>views/master/default.v.php</strong></p>

		<?php

			$this->renderMainView();

		?>
		
		<p>
			<ul>
				<li>
					<a href="<?=$this->url() ?>">Home</a>
				</li>
				<li>
					<a href="<?=$this->url('page1') ?>">Page 1</a>
				</li>
				<li>
					<a href="<?=$this->url('default/main2/page2') ?>">Page 2 (different main)</a>
				</li>
				<li>
					<a href="<?=$this->url('master2/default/page1') ?>">Page 1 with different master</a>
				</li>
				<li>
					<a href="<?=$this->url('error') ?>">Error handling</a>
				</li>
			</ul>
		</p>
	</body>
</html>