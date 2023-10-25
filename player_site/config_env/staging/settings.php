<?php

    /*
	|--------------------------------------------------------------------------
	| TOURNAMENT
	|-------------------------------------------------------------------------
	|
	*/
	$config['TOURNAMENT_SENDER'] = 'lp_dev@leekie.com';

    /*
	|--------------------------------------------------------------------------
	| TOURNAMENT
	|-------------------------------------------------------------------------
	|
	*/
	$config['assets']['rso_url']              = 'https://www.rsolab.com';
	$config['assets']['path']                 = '/ps/'.basename(dirname(dirname(__DIR__))).'/';
	$config['assets']['wl_path']              = '/ps/'.basename(dirname(dirname(__DIR__))).'/';
	$config['assets']['common_path']          = '/ps/'.basename(dirname(dirname(__DIR__))).'/';
	$config['assets']['wl_fallback_path']     = 'https://www.338alab.com/assets/';
	$config['assets']['common_fallback_path'] = 'https://www.338alab.com/assets/';
	$config['assets']['rso_folder']           = basename(dirname(dirname(__DIR__)));

    /*
	|--------------------------------------------------------------------------
	| SPORTS
	|-------------------------------------------------------------------------
	|
	*/
	$config['SPORTS_LOBBY_URL'] ='https://sportsbook.338a.com/web-root/restricted/default.aspx?theme=black&oddstyle=id';
	$config['sports']['bsi_src']='https://sportsbook.338a.com/web-root/restricted/default.aspx?theme=black&oddstyle=id';

    /*
	|--------------------------------------------------------------------------
	| WHITE LABEL
	|-------------------------------------------------------------------------
	|
	*/
	$config['WL_CODE']        = '';
	$config['WL_IS_ORIGINAL'] = 1;

    /*
	|--------------------------------------------------------------------------
	| EMAIL USER
	|-------------------------------------------------------------------------
	|
	*/
	$config['EMAIL_SENDER']   = 'support@338a.com';
	$config['EMAIL_RECEIVER'] = 'lp_dev@leekie.com';

    /*
	|--------------------------------------------------------------------------
	| REGISTRATION
	|-------------------------------------------------------------------------
	|
	*/
	$config['REGISTRATION_PARENTS'] = array(15);

    /*
	|--------------------------------------------------------------------------
	| RSO
	|-------------------------------------------------------------------------
	*/
	$config['rso']['all']['assets']                  = '/ps/'.basename(dirname(dirname(__DIR__))).'/';
	$config['rso']['all']['wl_assets']               = '/ps/'.basename(dirname(dirname(__DIR__))).'/';
	$config['rso']['fallback']['wl_assets_frontend'] = 'https://www.DOMAIN.com/assets/';
	$config['rso']['compress']                       = true;

	$config['WS_CONNECTION'] = replace_subdomain($_SERVER['HTTP_HOST'], 'wss://ws');
	