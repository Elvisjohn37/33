<?php
	$config = [
	/*
		|--------------------------------------------------------------------------
		| PDO Fetch Style
		|--------------------------------------------------------------------------
		|
		| By default, database results will be returned as instances of the PHP
		| stdClass object; however, you may desire to retrieve records in an
		| array format for simplicity. Here you can tweak the fetch style.
		|
		*/

		'fetch' => PDO::FETCH_CLASS,

		/*
		|--------------------------------------------------------------------------
		| Migration Repository Table
		|--------------------------------------------------------------------------
		|
		| This table keeps track of all the migrations that have already run for
		| your application. Using this information, we can determine which of
		| the migrations on disk haven't actually been run in the database.
		|
		*/

		'migrations' => 'migrations',
	];

	// connect to frontend root config
	require dirname(base_path()).'/'.PROJECT_DIR.'/config/'.basename(__FILE__);

	// connect to per environment config
	require dirname(__DIR__).'/config_env/'.ENVIRONMENT.'/'.basename(__FILE__);

	return $config;