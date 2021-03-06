<?php

	return [

		// this is application version
		// should be identical with GIT branch name
		'app_version' => 0.0,

		// required zEngine major version (integer value)
		'require_z_version' => 4,

		// this is minimum required zEngine version
		'minimum_z_version' => 4.3,

		'site_title' => 'Your site title',
		'site_description' => 'Your site description.',
		'site_author' => 'You',
		'site_keywords' => 'comma,separated,keywords',

		// modules that are enabled by default
		'default_modules' => ['auth', 'admin'],

		// modules that are not enabled by default, but need to be installed
		'also_install_modules' => [],

		// will be used to create all link urls, no trailing slash
		'base_url' => 'http://localhost',

		// if turned on, display message of unrecoverable error
		// turn this off in production!
		'debug_mode' => true,

		// redirect here in case of unrecoverable error
		// only applies when debug_mode is off
		'error_page' => 'error.html',

		// URL query variable name used for return paths
		'return_path_name' => 'r',

	];
