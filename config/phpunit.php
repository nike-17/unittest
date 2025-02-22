<?php

return array
(
	// The only enviroment in which the web runner is allowed to run
	// You can run tests from phpunit cli command reguardless of this setting
	// This can also be set to an array for multiple enviroments
	'enviroment'		=> Kohana::DEVELOPMENT,

	// This is the folder where we generate and zip all the reports for downloading
	// Needs to be readable and writable
	'temp_path'				=> Kohana::$cache_dir.'/phpunit',
	// The URL to redirect to after the report has been generated (for HTML only)
	'coverage_url'          => 'http://url/to/reports/',

	// If you don't use a whitelist then only files included during the request will be counted
	// If you do, then only whitelisted items will be counted
	'use_whitelist'		=> false,

	// Items to whitelist, only used in cli
	// Web runner ui allows user to choose which items to whitelist
	'whitelist'	=>	array
					(
						// Should the app be whitelisted?
						// Useful if you just want to test your application
						'application'		=> TRUE,
						// Set to array(TRUE) to include all modules, or use an array of module names
						// (the keys of the array passed to Kohana::modules() in the bootstrap)
						// Or set to FALSE to exclude all modules
						'modules'	=> false,
						'system' => false
						
					),
	
	// Does what it says on the tin
	// Blacklisted files won't be included in code coverage reports
	// If you use a whitelist then the blacklist will be ignored
	'use_blacklist'		=> TRUE,

	'blacklist'	=>	array
					(

						'system'	=> TRUE,
					),
	
	// A database connection that can be used when testing
	// This doen'st overwrite anything, tests will have to use this value manually
	'db_connection'	=> 'unit_testing',

);
