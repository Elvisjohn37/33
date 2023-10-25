<?php
	
    /*
	|--------------------------------------------------------------------------
	| Web Socket
	|--------------------------------------------------------------------------
	| WS_HOST      : Server that host the WebSocket Connection
	| WS_CONNECTION: URL use in frontend to subscribe to WS
	|
	*/
	$config['WS_HOST']       = 'https://wgs-vip.local.338a.leekie.com';
	$config['WS_CONNECTION'] = 'wss://ws.338aloc.com';
	$config['WS_PORT']       = '9301';

    /*
	|--------------------------------------------------------------------------
	| SEND_MAIL
	|--------------------------------------------------------------------------
	|
	| Allow system to send e-mail to real recipient. False, send to
    | specific e-mail only.
	|
	*/
	$config['SEND_MAIL'] = false;
        
    /*
	|--------------------------------------------------------------------------
	| log
	|--------------------------------------------------------------------------
	|
	| contains settings for our logger
	|
	*/

	$config['log']['storage'] = dirname(base_path()).'/ps_backend/storage/logs/';

    /*
	|--------------------------------------------------------------------------
	| RSO paths (older)
	|--------------------------------------------------------------------------
	|
	*/
	$config['resource_path_int']          = 'C:\xampp\htdocs\rso';
	$config['rso_absolute_path_internal'] = 'C:\xampp\htdocs\rso';
	$config['avatar_folder_int']          = 'C:\xampp\htdocs\rso\avatar\\';
	$config['avatar_folder_ext']          = 'https://localhost/rso/avatar/';

	/*
	|--------------------------------------------------------------------------
	| TOURNAMENT ENAIL SENDER
	|--------------------------------------------------------------------------
	*/
	$config['TOURNAMENT_SENDER'] = 'lp_dev@leekie.com';


	/*
	|--------------------------------------------------------------------------
	| API
	|--------------------------------------------------------------------------
	*/
	$config['API_URL'] = 'https://ogs01lab.leekie.com/onyx_gs/';

	/*
	|--------------------------------------------------------------------------
	| TOGEL API
	|--------------------------------------------------------------------------
	*/
	$config['TOGEL_LOBBY_URL'] = 'http://sas01lab.leekie.com:2030/togel_lobby_information';
	
	/*
	|--------------------------------------------------------------------------
	| rso
	|--------------------------------------------------------------------------
	|
	| PS own assets settings
	|
	*/
	$config['rso']['frontend_url'] 		                = 'https://www.rsoloc.com:9402';
	$config['rso']['compress']                          = 1;
	$config['rso']['backend_url']  		                = '/var/www/html/rso';
	$config['rso']['detect_path'] 		                = 'images/favicon/favicon32.png';
	$config['rso']['self_hosted_dirname']               = 'assets_sys';
	$config['rso']['fallback']['common_assets_frontend']= 'https://www.338aloc.com/assets/';
	$config['rso']['fallback']['assets_frontend']       = 'https://www.DOMAIN.com/assets/';
	$config['rso']['all']	                            = array(
															'assets' 			 => '/assets/',
															'wl_assets' 		 => '/assets/',
															'common_assets' 	 => '/ps/player_site/',
															'bannerspromotions'	 => '/banners_promotions/',
															'gameguide'          => '/game_guide/',
															'avatar_ext'         => '/avatar/'
														);

	// connect to frontend per environment config
	require dirname(base_path()).'/'.PROJECT_DIR.'/config_env/'.ENVIRONMENT.'/'.basename(__FILE__);