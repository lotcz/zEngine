<?php

	if (isset($this->z->core->data['form'])) {
		$this->z->forms->renderForm($this->z->core->data['form']);
	}
	if (isset($this->z->core->data['table'])) {
		$this->z->tables->renderTable($this->z->core->data['table']);
	}