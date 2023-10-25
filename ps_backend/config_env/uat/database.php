<?php
	
	/*
	|--------------------------------------------------------------------------
	| Default Database Connection Name
	|--------------------------------------------------------------------------
	|
	| Here you may specify which of the database connections below you wish
	| to use as your default connection for all database work. Of course
	| you may use many connections at once using the Database library.
	|
	*/

	$config['default'] = 'uat';

	/*
	|--------------------------------------------------------------------------
	| Database Connections
	|--------------------------------------------------------------------------
	|
	| Here are each of the database connections setup for your application.
	| Of course, examples of configuring each database platform that is
	| supported by Laravel is shown below to make development simple.
	|
	|
	| All database work in Laravel is done through the PHP PDO facilities
	| so make sure you have the driver for your particular database of
	| choice installed on your machine before you begin development.
	|
	*/
	$config['connections'] = array(

		'uat' => array(
			'driver'	=>	'mysql',
			'host'		=>	'mdb01',
			'database'	=>	'onyx',
			'username'	=>	'player_site',
			'password'	=>	'Tun4Sp1cyM4k1',
			'charset'	=>	'utf8',
			'collation'	=>	'utf8_unicode_ci',
			'prefix'	=>	'',
		),

	);

	/*
	|--------------------------------------------------------------------------
	| Redis Databases
	|--------------------------------------------------------------------------
	|
	| Redis is an open source, fast, and advanced key-value store that also
	| provides a richer set of commands than a typical key-value systems
	| such as APC or Memcached. Laravel makes it easy to dig right in.
	|
	*/

	$config['redis'] = array(

		'cluster' => false,

		'default' => array(
			'host'     => 'redis-vip.uat.338a.'.HOST_TLD,
			'port'     => 6379,
			'database' => 0,
		),

	);
	
	// connect to frontend per environment config
	require dirname(base_path()).'/'.PROJECT_DIR.'/config_env/'.ENVIRONMENT.'/'.basename(__FILE__);
	
