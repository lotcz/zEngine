<?php
	require_once '../../src/z.php';	
	$z = new zEngine('app/');
	$z->enableModule('mysql');
	$z->run();