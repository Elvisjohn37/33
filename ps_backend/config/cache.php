<?php
$config = array(

	/*
	|--------------------------------------------------------------------------
	| Default Cache Driver
	|--------------------------------------------------------------------------
	|
	| This option controls the default cache "driver" that will be used when
	| using the Caching library. Of course, you may use other drivers any
	| time you wish. This is the default when another is not specified.
	|
	| Supported: "file", "database", "apc", "memcached", "redis", "array"
	|
	*/

	'default' => 'redis',

    /*
    |--------------------------------------------------------------------------
    | Cache Stores
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the cache "stores" for your application as
    | well as their drivers. You may even define multiple stores for the
    | same cache driver to group types of items stored in your caches.
    |
    */

    'stores' => array(
    
                'apc' => array(
                            'driver' => 'apc',
                        ),

                'array' => array(
                                    'driver' => 'array',
                            ),

                'database' => array(
                                'driver' => 'database',
                                'table'  => 'cache',
                                'connection' => null,
                            ),

                'file' => array(
                            'driver' => 'file',
                            'path'   => '/var/www/html/rso/ps_cache',
                        ),

                'memcached' => array(
                                'driver'  => 'memcached',
                                'servers' => array(
                                                array(
                                                    'host' => '127.0.0.1', 'port' => 11211, 'weight' => 100,
                                                ),
                                            ),
                            ),

                'redis' => array(
                            'driver' => 'redis',
                            'connection' => 'default',
                        ),

    ),

	/*
	|--------------------------------------------------------------------------
	| Cache Key Prefix
	|--------------------------------------------------------------------------
	|
	| When utilizing a RAM based store such as APC or Memcached, there might
	| be other applications utilizing the same cache. So, we'll specify a
	| value to get prefixed to all our keys so we can avoid collisions.
	|
	*/

	'prefix' => 'laravel',

);


// connect to frontend root config
require dirname(base_path()).'/'.PROJECT_DIR.'/config/'.basename(__FILE__);

// connect to per environment config
require dirname(__DIR__).'/config_env/'.ENVIRONMENT.'/'.basename(__FILE__);

return $config;