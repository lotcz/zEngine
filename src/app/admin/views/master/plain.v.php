<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<script src="https://code.jquery.com/jquery-3.5.1.min.js" crossorigin="anonymous"></script>
		<?php
			$this->renderIncludes('admin.head');
			$this->renderIncludes('admin.top');
		?>
	</head>

	<body>
		<?php
			$this->renderPageView();
			$this->renderIncludes('admin.default');
			$this->renderIncludes('admin.bottom');
		?>
	</body>
</html>
