<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<?php
			$this->renderIncludes('head');
			$this->renderIncludes('top');
		?>
	</head>

	<body>
		<?php
			$this->renderMessages();
			$this->renderPageView();
			$this->renderIncludes('default');
			$this->renderIncludes('bottom');
		?>
	</body>
</html>
