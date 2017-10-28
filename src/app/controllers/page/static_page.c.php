<?php

	$static_page = new StaticPageModel($this->db, $this->getPath(-1));

	if ($static_page->is_loaded) {
		$this->setPageTitle($static_page->val('static_page_title'));
		$this->setData('static_page_content', $static_page->val('static_page_content'));
	} else {
		$this->redirect('notfound');
	}
