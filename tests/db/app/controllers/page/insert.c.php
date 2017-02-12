<?php
	require_once __DIR__ . '/../../models/test.m.php';
	
	$this->data['page_title'] = 'Insert';
	
	if (isPost()) {
		$test = new TestModel($this->db);
		$test->set('test_value', get('test_value'));
		$test->save();
		$this->data['message'] = sprintf('Record saved with id <strong>%s</strong>.', $test->val('test_id'));
	}