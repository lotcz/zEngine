<div class="container p-3">
	<h1><?=$this->getData('page_title') ?></h1>
	
	<?php	
		$this->renderMessages();
		$this->renderPageView();
	?>
</div>