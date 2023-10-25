<?php
	
    /*
	|--------------------------------------------------------------------------
	| Web Socket
	|--------------------------------------------------------------------------
	|
	| WS_HOST      : Server that host the WebSocket Connection
	| WS_CONNECTION: URL use in frontend to subscribe to WS
	|
	*/
    
	$config['WS_HOST']	     = 'https://wgs-vip.338a.'.HOST_TLD;
	$config['WS_CONNECTION'] = replace_subdomain($_SERVER['HTTP_HOST'], 'wss://ws');

    /*
	|--------------------------------------------------------------------------
	| Announcement Resources
	|--------------------------------------------------------------------------
	|
	*/
	$config['ANN_RESX']	= 'https://ars.DOMAIN.com';

    /*
	|--------------------------------------------------------------------------
	| SEND_MAIL
	|--------------------------------------------------------------------------
	|
	| Allow system to send e-mail to real recipient. False, send to
    | specific e-mail only.
	|
	*/
	$config['SEND_MAIL'] = true;

    /*
	|--------------------------------------------------------------------------
	| RSO Paths
	|-------------------------------------------------------------------------
	|
	*/
	$config['resource_path_int']          = 'https://rso-vip.338a.'.HOST_TLD;
	$config['resource_path'] 	          = 'https://rso.DOMAIN.com';
	$config['rso_absolute_path_internal'] =	'/var/www/html/rso';
	$config['rso_absolute_path'] 		  = 'https://rso.DOMAIN.com';

    /*
	|--------------------------------------------------------------------------
	| Avatar Reource Paths
	|-------------------------------------------------------------------------
	|
	*/
	$config['avatar_folder_int'] =	'/var/www/html/rso/avatar/';
	$config['avatar_folder_ext'] = 'https://rso.DOMAIN.com/avatar/';

	$config['LIVEGAMES'] = 0;

	$config['LIVECHAT_PLUGIN'] = 3;

	$config['assets']['compress']   = 1;
	$config['assets']['rso_folder'] = basename(dirname(dirname(__DIR__)));

	// Claim Tournament Prize
	$config['TOURNAMENT_SENDER'] = 'goc@leekie.com';

	// SPORTS BET
	$config['API_URL'] = 'https://mgs-vip.338a.'.HOST_TLD.'/onyx_gs/';

	// WL settings
	$config['TOGEL_LOBBY_URL']	=	'http://sas01.'.HOST_TLD.':2030/togel_lobby_information';

	// update rso configs
	$config['rso']['frontend_url']                       = 'https://onyx.628c.com';
	$config['rso']['backend_url']                        = '/var/www/html/rso';
	$config['rso']['all']['common_assets']               = '/ps/player_site/';
	$config['rso']['compress']                           = 1;
	$config['rso']['detect_path']                        = 'images/favicon/favicon32.png';
	$config['rso']['fallback']['common_assets_frontend'] = 'https://www.338a.com/assets/';

	/*
	|--------------------------------------------------------------------------
	| savvy
	|--------------------------------------------------------------------------
	|
	| Savvy assets and plugin settings
	|
	*/
    $config['savvy']['announcement'] = array(
										'css' => 'https://ars.DOMAIN.com/css/jquery.marquee.css',
										'js'  => 'https://ars.DOMAIN.com/scripts/savvy_announcement.js'
									);
    

	// connect to frontend per environment config
	require dirname(base_path()).'/'.PROJECT_DIR.'/config_env/'.ENVIRONMENT.'/'.basename(__FILE__);


