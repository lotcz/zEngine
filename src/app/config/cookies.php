<?php

	return [


		// show warning when cookies are disabled in the browser
		// can be customized in 'cookies-disabled' partial view
		// text is specified by '--cookies-disabled--' localizable string
		'show_disabled' => true,
		'disabled_placement' => 'top',

		// show warning that this site uses 3rd party cookies
		// can be customized in 'cookies-warning' partial view
		// text is specified by '--cookies-warning--' localizable string
		'show_warning' => false,
		'warning_placement' => 'top',

		'warning_confirmed_cookie_name' => 'cookies_warning_confirmed'
	];
