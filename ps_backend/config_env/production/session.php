<?php

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Domain
    |--------------------------------------------------------------------------
    | Set this per environment on demand 
    | to minimize the loading cost of processing this
    |
    */
	$config['domain'] = remove_subdomain(custom_parse_url($_SERVER['HTTP_HOST'])['host']);

    // connect to frontend per environment config
    require dirname(base_path()).'/'.PROJECT_DIR.'/config_env/'.ENVIRONMENT.'/'.basename(__FILE__);