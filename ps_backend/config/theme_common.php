<?php

$config = array();

// connect to frontend root config
require dirname(base_path()).'/'.PROJECT_DIR.'/config/'.basename(__FILE__);

// connect to per environment config
require dirname(__DIR__).'/config_env/'.ENVIRONMENT.'/'.basename(__FILE__);

return $config;

