<?php
	require_once __DIR__ . '/zengine.php';	
	$z = new zEngine(__DIR__ . '/app/');	
	$z->enableModule('mysql');
	$sql = file_get_contents(__DIR__ . '/../sql/zEngine.sql');
	zQuery::executeSQL($z->mysql->connection, $sql);