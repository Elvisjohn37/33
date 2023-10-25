<?php

// environment settings
define('ENVIRONMENT', get_host_env());
define('HOST_TLD', get_last_segment(gethostname(),'.', 2));

$app->detectEnvironment(function() { 
	return ENVIRONMENT;
});
