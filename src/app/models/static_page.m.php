<?php

class StaticPageModel extends zModel {

	public $table_name = 'static_pages';
	public $id_name = 'static_page_id';

	public function getAliasUrl() {
			return $this->val('static_page_title');		
	}

	public function getViewPath() {
		return 'default/default/static_page/' . $this->val('static_page_id');
	}

	public function getLinkPath() {
		if (strlen($this->val('alias_url')) > 0) {
			$url = $this->val('alias_url');
		} else {
			$url = $this->getViewPath();
		}
		return $url;
	}

}
