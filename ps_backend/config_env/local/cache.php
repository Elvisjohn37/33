<?php

    /*
    |--------------------------------------------------------------------------
    | Cache Stores (File)
    |--------------------------------------------------------------------------
    |
    */

    $config['stores']['file'] = array(
							        'driver' => 'file',
							        'path'   => storage_path('../../ps_cache'),
							    );

    // connect to frontend per environment config
    require dirname(base_path()).'/'.PROJECT_DIR.'/config_env/'.ENVIRONMENT.'/'.basename(__FILE__);