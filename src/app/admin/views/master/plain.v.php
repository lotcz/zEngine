<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">		
		<?php		
			$this->renderIncludes('head');
		?>				
	</head>

	<body>	
		<?php
			$this->renderPageView();
			$this->renderIncludes('default');
			$this->renderIncludes('bottom');
		?>
	</body>
</html>