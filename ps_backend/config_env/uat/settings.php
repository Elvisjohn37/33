<?php
	
    /*
	|--------------------------------------------------------------------------
	| Web Socket
	|--------------------------------------------------------------------------
	|
	| WS_HOST: Server that host the WebSocket Connection
	|
	*/
	$config['WS_HOST'] = 'https://wgs-vip.uat.338a.'.HOST_TLD;

    /*
	|--------------------------------------------------------------------------
	| Announcement Resources
	|-------------------------------------------------------------------------
	|
	*/
	$config['ANN_RESX'] = 'https://ars.DOMAIN.com';

    /*
	|--------------------------------------------------------------------------
	| RSO Paths
	|-------------------------------------------------------------------------
	|
	*/
	$config['resource_path'] 	          = 'https://rso.DOMAIN.com';
	$config['resource_path_int']          = 'https://rso-vip.uat.338a.'.HOST_TLD;
	$config['rso_absolute_path']          = 'https://rso.DOMAIN.com';
	$config['rso_absolute_path_internal'] =	'/var/www/html/rso';

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

	$config['LIVEGAMES'] = 0;
	$config['LIVECHAT_PLUGIN'] = 3;

	$config['assets']['compress'] = 1;

	// Claim Tournament Prize
	$config['TOURNAMENT_SENDER'] = 'sc@leekie.com';

	// SPORTS BET
	$config['API_URL'] = 'https://mgs-vip.uat.338a.'.HOST_TLD.'/onyx_gs/';

	// WL settings
	$config['TOGEL_LOBBY_URL']	=	'http://sas01uat.'.HOST_TLD.':2030/togel_lobby_information';

	// update rso configs
	$config['rso']['frontend_url']                      = 'https://www.tryrso.com';
	$config['rso']['backend_url']                       =  '/var/www/html/rso';
	$config['rso']['compress']                          =  1;
	$config['rso']['detect_path']                       = 'images/favicon/favicon32.png';
	$config['rso']['self_hosted_dirname']               = 'assets_sys';
	$config['rso']['fallback']['common_assets_frontend']= 'https://www.try338a.com/assets/';
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

