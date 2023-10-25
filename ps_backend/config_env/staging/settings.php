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
    
	$config['WS_HOST']	     = 'https://wgs-vip.lab.338a.'.HOST_TLD;
	$config['WS_CONNECTION'] = 'wss://ws.338alab.com';

    /*
	|--------------------------------------------------------------------------
	| Announcement Resources
	|-------------------------------------------------------------------------
	|
	*/
	$config['ANN_RESX']	 = 'https://ars.DOMAIN.com';

    /*
	|--------------------------------------------------------------------------
	| RSO Paths
	|-------------------------------------------------------------------------
	|
	*/
	$config['resource_path']              = 'https://rso.DOMAIN.com';
	$config['resource_path_int']          = '/var/www/html/rso';
	$config['rso_absolute_path'] 		  = 'https://www.rsolab.com';
	$config['rso_absolute_path_internal'] = '/var/www/html/rso';

    /*
	|--------------------------------------------------------------------------
	| Avatar Reource Paths
	|-------------------------------------------------------------------------
	|
	*/
	$config['avatar_folder_ext'] = 'https://rso.DOMAIN.com/avatar/'; 
	$config['avatar_folder_int'] = '/var/www/html/rso/avatar/';


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

	$config['assets']['compress'] = 0;

	// SPORTS BET
	$config['API_URL'] 						=   'https://ogs01lab.'.HOST_TLD.'/onyx_gs/';

	// WL settings
	$config['TOGEL_LOBBY_URL']				=	'http://sas01lab.'.HOST_TLD.':2030/togel_lobby_information';

	$config['rso']['frontend_url'] 		                = 'https://www.rsolab.com';
	$config['rso']['compress']                          = 1;
	$config['rso']['backend_url']  		                = '/var/www/html/rso';
	$config['rso']['detect_path'] 		                = 'images/favicon/favicon32.png';
	$config['rso']['self_hosted_dirname']               = 'assets_sys';
	$config['rso']['fallback']['common_assets_frontend']= 'https://www.338alab.com/assets/';
	$config['rso']['all']	                            = array(
															'assets' 			 => '/assets/',
															'wl_assets' 		 => '/assets/',
															'common_assets' 	 => '/ps/player_site/',
															'bannerspromotions'	 => '/banners_promotions/',
															'gameguide'          => '/game_guide/',
															'avatar_ext'         => '/avatar/'
														);
	
	$config['savvy']['announcement'] = array(
										'css' => 'https://ars.DOMAIN.com/css/jquery.marquee.css',
										'js'  => 'https://ars.DOMAIN.com/scripts/savvy_announcement.js'
									);
	

	// connect to frontend per environment config
	require dirname(base_path()).'/'.PROJECT_DIR.'/config_env/'.ENVIRONMENT.'/'.basename(__FILE__);

