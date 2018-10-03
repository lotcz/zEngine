<?php

	return [

		// this is application version
		// should be identical with GIT branch name
		'version' => 3.0,

    // required zEngine major version (integer value)
		'require_z_version' => 3,

		// this is minimum required zEngine version
		'minimum_z_version' => 3.0,

		// modules that are enabled by default
		'modules' => ['resources', 'db', 'i18n', 'auth', 'admin'],

		// modules that are not enabled by default, but need to be installed
		'also_install' => []

	];
