<div class="container-fluid p-md-3">
	<h1 class="main-title"><?=$this->getData('page_title') ?></h1>

	<?php
		$this->renderMessages();
		$this->renderPageView();
		$this->renderMessages();
	?>
</div>
