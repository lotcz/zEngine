<?php
	require_once __DIR__ . '/../../models/test.m.php';
	
	$items = TestModel::all($this->db);
	
	$this->data['count'] = count($items);