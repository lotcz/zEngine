<?php
	require_once __DIR__ . '/../../models/test.m.php';
	
	$this->data['page_title'] = 'Select';
	
	$this->data['items'] = TestModel::all($this->db);