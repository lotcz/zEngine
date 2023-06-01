<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<?php
			$this->renderIncludes('admin.head');
			$this->renderIncludes('admin.top');
		?>
	</head>

	<body>
		<?php
			$this->renderMessages();
			$this->renderPageView();
			$this->renderIncludes('admin.default');
			$this->renderIncludes('admin.bottom');
		?>
	</body>
</html>
