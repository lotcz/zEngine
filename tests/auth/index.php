<?php
	require_once '../../src/z.php';	
	$z = new zEngine('app/');	
	$z->enableModule('mysql');
	$z->enableModule('i18n');
	$z->enableModule('messages');
	$z->enableModule('auth');
	$z->run();